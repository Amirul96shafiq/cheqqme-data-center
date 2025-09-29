# Daily Logging Setup

This project uses Laravel's standard daily log rotation for better log management. Here's how it works:

## Current Setup

### Daily Log Files

-   **Today's log**: `laravel-{YYYY-MM-DD}.log` (e.g., `laravel-2025-09-29.log`)
-   **Standard Format**: Laravel's built-in daily rotation
-   **Rotation**: Laravel automatically rotates logs daily
-   **Retention**: Logs are kept for 30 days by default

### How It Works

1. **Daily Rotation**: Laravel automatically rotates logs daily using `RotatingFileHandler`
2. **Standard Format**: Uses Laravel's built-in daily logging format
3. **Helper Service**: `App\Services\DailyLogService` provides helper methods
4. **Setup Command**: `php artisan log:setup-daily` manages log file initialization

## Usage

### Using Standard Laravel Logging

```php
// Standard Laravel logging (automatically goes to daily files)
Log::info('User logged in', ['user_id' => 123]);
Log::error('Database connection failed', ['error' => $exception]);
```

### Using DailyLogService (Additional Features)

```php
use App\Services\DailyLogService;
use App\Services\DailyLogCounterService;

// Automatically writes to today's counter-based log file
DailyLogService::info('Custom log message', ['context' => 'data']);
DailyLogService::error('Error occurred', ['error' => $error']);

// Get specific log file paths
$todayLog = DailyLogService::getTodayLogPath();
$yesterdayLog = DailyLogService::getYesterdayLogPath();
$counterNumber = DailyLogCounterService::getTodayLogNumber();

// Reset counter if needed (admin use)
DailyLogCounterService::resetCounter();
```

## File Naming Convention

-   **Format**: `laravel-{YYYY-MM-DD}.log`
-   **Today (Sep 29, 2025)**: `laravel-2025-09-29.log`
-   **Tomorrow (Sep 30, 2025)**: `laravel-2025-09-30.log`
-   **Day After Tomorrow (Oct 1, 2025)**: `laravel-2025-10-01.log`

Uses Laravel's standard daily rotation format.

## Laravel Boost Integration

The AI assistant can easily identify and read daily logs using the `DailyLogService`:

### For Today's Logs

```php
$todayLog = DailyLogService::getTodayLogPath();
// Returns: G:\projects\cheqqme-data-center\storage\logs\laravel_000001.log

$counterNumber = DailyLogCounterService::getTodayLogNumber();
// Returns: "000001"
```

### For Counter Management

```php
// Check counter status
php artisan log:counter

// Reset counter (admin use)
php artisan log:counter --reset
```

### Reading Log Content

```php
// Standard file reading
$content = file_get_contents(DailyLogService::getTodayLogPath());

// Or use Laravel Boost tools
mcp_laravel-boost_read-log-entries(entries: 50)
```

## Commands

### Setup Daily Logging

```bash
# Initialize today's log file with incrementing counter
php artisan log:setup-daily

# Setup with old log cleanup
php artisan log:setup-daily --clean-old

# Check current counter status
php artisan log:counter

# Reset counter (admin use)
php artisan log:counter --reset
```

## Benefits

1. **Smaller Files**: Each day gets its own log file, preventing massive single files
2. **Easy Retrieval**: AI can easily find logs for specific dates
3. **Automatic Rotation**: Laravel handles rotation automatically
4. **Version Control**: Historical logs are preserved with clear date stamps
5. **Performance**: Faster log reading due to smaller file sizes

## Configuration

The daily logging is configured in `config/logging.php`:

```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => env('LOG_DAILY_DAYS', 30),
    'replace_placeholders' => true,
],
```

Default log stack now uses daily rotation:

```php
'stack' => [
    'driver' => 'stack',
    'channels' => explode(',', (string) env('LOG_STACK', 'daily')),
    'ignore_exceptions' => false,
],
```

## Archive Information

The original large `laravel.log` file (194MB) has been archived as:

-   `laravel_092925_1759124509.log` (old format) - Contains all historical logs

This archived file can be kept for reference or deleted if no longer needed.
