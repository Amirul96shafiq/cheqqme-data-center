<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WarmActionBoardCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'action-board:warm-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up Action Board cache for better performance (Trello-style optimization)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Warming up Action Board cache...');

        // Cache users for form selects and task assignments
        $this->info('Caching users...');
        Cache::put('kanban_users_cache',
            User::select('id', 'name', 'username', 'short_name', 'email')
                ->withTrashed()
                ->get()
                ->keyBy('id'),
            300
        );

        // Cache column counts
        $this->info('Caching column counts...');
        $columnCounts = DB::table('tasks')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        Cache::put('kanban_column_counts', $columnCounts, 60);

        // Pre-cache first batch of tasks for each column
        $this->info('Pre-caching initial tasks...');
        $columns = ['todo', 'in_progress', 'toreview', 'completed', 'archived'];

        foreach ($columns as $status) {
            $tasks = Task::where('status', $status)
                ->with(['comments' => function ($query) {
                    $query->select('task_id', 'id');
                }])
                ->withCount('comments')
                ->select([
                    'id', 'title', 'description', 'status', 'order_column',
                    'due_date', 'assigned_to', 'client', 'project', 'document',
                    'important_url', 'attachments', 'extra_information',
                ])
                ->orderBy('order_column')
                ->limit(50)
                ->get();

            $cacheKey = "action_board_initial_{$status}";
            Cache::put($cacheKey, $tasks, 120);

            $this->info("Cached {$tasks->count()} tasks for {$status} column");
        }

        // Cache frequently accessed data
        $this->info('Caching frequently accessed data...');

        // Cache recent task IDs for quick lookups
        $recentTaskIds = Task::latest('updated_at')
            ->limit(100)
            ->pluck('id')
            ->toArray();

        Cache::put('recent_task_ids', $recentTaskIds, 300);

        // Cache task stats
        $taskStats = [
            'total_tasks' => Task::count(),
            'tasks_by_status' => $columnCounts,
            'overdue_tasks' => Task::where('due_date', '<', now())
                ->where('status', '!=', 'completed')
                ->count(),
            'due_today' => Task::whereDate('due_date', today())
                ->where('status', '!=', 'completed')
                ->count(),
        ];

        Cache::put('action_board_stats', $taskStats, 300);

        $this->info('✅ Action Board cache warmed successfully!');
        $this->info('📊 Stats:');
        $this->info('   - Total tasks: '.$taskStats['total_tasks']);
        $this->info('   - To Do: '.($columnCounts['todo'] ?? 0));
        $this->info('   - In Progress: '.($columnCounts['in_progress'] ?? 0));
        $this->info('   - To Review: '.($columnCounts['toreview'] ?? 0));
        $this->info('   - Completed: '.($columnCounts['completed'] ?? 0));
        $this->info('   - Archived: '.($columnCounts['archived'] ?? 0));

        return Command::SUCCESS;
    }
}
