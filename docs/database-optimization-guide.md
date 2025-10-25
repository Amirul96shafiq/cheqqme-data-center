# Database Query Optimization Guide

## Overview

This document provides guidelines for optimizing database queries in the CheQQme Data Center application to improve page load times and overall performance.

## Current Status

The application uses:

-   **SQLite** database
-   **Eloquent ORM** for queries
-   **Filament Resources** for CRUD operations
-   **Livewire Components** for reactive interfaces

## Optimization Strategies

### 1. Eager Loading (N+1 Query Prevention)

#### Problem

In `TaskResource.php`, the `getGlobalSearchResultDetails` method calls `assignedToUsers()` without eager loading, causing N+1 queries.

```php
// ❌ Bad: Causes N+1 queries
public static function getGlobalSearchResultDetails($record): array
{
    $assignedUsers = $record->assignedToUsers();
    // ...
}
```

#### Solution

Use eager loading in the resource's query modifications:

```php
// ✅ Good: Eager load relationships
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with(['assignedUsers', 'project', 'creator']);
}
```

### 2. Optimize Filament Table Queries

For Filament resources, optimize table queries:

```php
// In your Resource class
public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(fn (Builder $query) => $query
            ->with(['assignedUsers', 'project']) // Eager load relationships
            ->select(['id', 'title', 'status', 'due_date', 'project_id']) // Select only needed columns
        );
}
```

### 3. Index Critical Columns

Ensure proper database indexing for frequently queried columns:

```php
// In your migration files
Schema::table('tasks', function (Blueprint $table) {
    $table->index('status'); // For filtering by status
    $table->index('due_date'); // For sorting by due date
    $table->index('project_id'); // For relationship queries
    $table->index(['status', 'due_date']); // Composite index for common queries
});
```

### 4. Cache Expensive Queries

For data that doesn't change frequently:

```php
use Illuminate\Support\Facades\Cache;

// Cache for 1 hour
$statistics = Cache::remember('dashboard-statistics', 3600, function () {
    return [
        'total_tasks' => Task::count(),
        'completed_tasks' => Task::where('status', 'completed')->count(),
        'active_projects' => Project::where('status', 'active')->count(),
    ];
});
```

### 5. Optimize Livewire Component Queries

In `TaskComments.php`:

```php
// ✅ Already optimized with eager loading
public function getCommentsProperty()
{
    return $this->task->comments()
        ->where('status', '!=', 'deleted')
        ->whereNull('parent_id')
        ->with([
            'user:id,name,username,avatar,email,timezone,country,cover_image,online_status,spotify_id,phone,phone_country',
            'reactions.user:id,name,username,avatar',
            'replies' => function ($query) {
                $query->where('status', '!=', 'deleted')
                    ->with(['user:id,name,username,avatar', 'reactions.user']);
            },
        ])
        ->orderByDesc('created_at')
        ->take($this->visibleCount)
        ->get();
}
```

### 6. Use Database Views for Complex Queries

Create database views for frequently accessed complex queries:

```php
// In a migration
DB::statement("
    CREATE VIEW active_tasks_with_users AS
    SELECT
        tasks.*,
        users.name as creator_name,
        projects.name as project_name
    FROM tasks
    LEFT JOIN users ON tasks.created_by = users.id
    LEFT JOIN projects ON tasks.project_id = projects.id
    WHERE tasks.status != 'archived'
");

// Use in your model
class ActiveTask extends Model
{
    protected $table = 'active_tasks_with_users';
}
```

### 7. Pagination Best Practices

Use cursor pagination for large datasets:

```php
// ✅ Better for large datasets
$tasks = Task::orderBy('created_at', 'desc')
    ->cursorPaginate(50);

// Instead of offset pagination
// ❌ Slower for large offsets
$tasks = Task::paginate(50);
```

### 8. Query Result Caching

Use model caching for frequently accessed records:

```php
// In your model
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    // Cache specific queries
    public static function getActiveTasksCount()
    {
        return Cache::remember('active_tasks_count', 300, function () {
            return static::whereIn('status', ['todo', 'in_progress', 'toreview'])->count();
        });
    }
}
```

## Implementation Checklist

-   [ ] Add indexes to frequently queried columns
-   [ ] Implement eager loading in all Resources
-   [ ] Add caching to dashboard statistics
-   [ ] Optimize Livewire component queries (Already done for TaskComments)
-   [ ] Use select() to limit columns when possible
-   [ ] Implement query result caching for expensive queries
-   [ ] Monitor query performance with Laravel Debugbar (in development)
-   [ ] Use database query logging to identify slow queries

## Monitoring

Use Laravel Telescope or Debugbar to monitor:

```bash
# Install Laravel Telescope (development only)
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

## Performance Testing

```bash
# Test query performance
php artisan tinker
>>> \DB::enableQueryLog();
>>> Task::with('assignedUsers')->get();
>>> dd(\DB::getQueryLog());
```

## SQLite Specific Optimizations

Since you're using SQLite:

```php
// In config/database.php
'sqlite' => [
    'driver' => 'sqlite',
    'database' => env('DB_DATABASE', database_path('database.sqlite')),
    'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
    // Add these for better performance
    'journal_mode' => 'WAL', // Write-Ahead Logging for better concurrency
    'synchronous' => 'NORMAL', // Faster writes
    'cache_size' => 5000, // Increase cache
    'temp_store' => 'MEMORY', // Use memory for temp tables
],
```

## Results Expected

After implementing these optimizations:

-   **50-70% reduction** in database queries per page
-   **30-50% faster** page load times
-   **Better user experience** with SPA navigation + optimized queries

## Next Steps

1. Run `php artisan optimize` after code changes
2. Clear cache with `php artisan cache:clear`
3. Rebuild assets with `npm run build`
4. Test performance with browser dev tools

