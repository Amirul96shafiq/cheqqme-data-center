<?php

declare(strict_types=1);

namespace App\Http\Livewire\Relaticle\Flowforge;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Relaticle\Flowforge\Livewire\KanbanBoard as BaseKanbanBoard;

class KanbanBoard extends BaseKanbanBoard
{
    // Properties for progressive loading (Trello-style)
    public $loadedTasksPerColumn = [
        'todo' => 0,
        'in_progress' => 0,
        'toreview' => 0,
        'completed' => 0,
        'archived' => 0,
    ];

    public $batchSize = 50; // Load 50 tasks per batch

    protected $listeners = [
        'refreshBoard' => 'optimizedRefreshBoard',
        'task-created' => 'optimizedRefreshBoard',
        'task-moved' => 'optimizedRefreshBoard',
    ];

    public ?string $search = null;

    /**
     * Override mount to implement progressive loading
     */
    public function mount(\Relaticle\Flowforge\Contracts\KanbanAdapterInterface $adapter, ?int $initialCardsCount = null, ?int $cardsIncrement = null, array $searchable = [], ?string $search = null): void
    {
        parent::mount($adapter, $initialCardsCount, $cardsIncrement, $searchable);
        $this->search = $search;
        $this->preloadUserCache();
    }

    /**
     * Cache users for 5 minutes to avoid repeated queries (Trello approach)
     */
    protected function preloadUserCache(): void
    {
        Cache::remember('kanban_users_cache', 300, function () {
            return User::select('id', 'name', 'username', 'short_name', 'email')
                ->withTrashed()
                ->get()
                ->keyBy('id');
        });
    }

    /**
     * Optimized refresh board with smart caching
     */
    public function optimizedRefreshBoard(): void
    {
        logger('KanbanBoard: optimizedRefreshBoard called');
        // Clear user cache to get fresh data
        Cache::forget('kanban_users_cache');
        $this->preloadUserCache();

        // Efficiently reload only visible tasks
        $this->refreshBoard();

        // Update task counts per column
        $this->updateColumnCounts();
    }

    /**
     * Update task counts per column efficiently
     */
    protected function updateColumnCounts(): void
    {
        $counts = Cache::remember('kanban_column_counts', 60, function () {
            return DB::table('tasks')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();
        });

        foreach ($this->loadedTasksPerColumn as $column => $loaded) {
            $this->loadedTasksPerColumn[$column] = min($loaded, $counts[$column] ?? 0);
        }
    }

    /**
     * Get cached users efficiently
     */
    protected function getCachedUsers()
    {
        return Cache::get('kanban_users_cache', collect());
    }

    /**
     * Update the order of cards in a column and log activity for each moved task.
     *
     * @param  int|string  $columnId  The column ID
     * @param  array  $cardIds  The card IDs in their new order
     * @return bool Whether the operation was successful
     */
    public function updateRecordsOrderAndColumn(int|string $columnId, array $cardIds): bool
    {
        logger()->debug('KANBAN LOGGING', [
            'columnId' => $columnId,
            'cardIds' => $cardIds,
            'user' => Auth::id(),
        ]);

        // Fetch all tasks before any update
        $tasks = Task::whereIn('id', $cardIds)->get()->keyBy('id');
        $originalStates = [];
        foreach ($cardIds as $order => $id) {
            if (isset($tasks[$id])) {
                $originalStates[$id] = [
                    'order_column' => $tasks[$id]->order_column,
                    'status' => $tasks[$id]->status,
                ];
            }
        }

        // Now update the records (this may update DB)
        $success = $this->adapter->updateRecordsOrderAndColumn($columnId, $cardIds);

        if ($success) {
            // Re-fetch tasks after update
            $updatedTasks = Task::whereIn('id', $cardIds)->get()->keyBy('id');
            foreach ($cardIds as $order => $id) {
                if (isset($originalStates[$id]) && isset($updatedTasks[$id])) {
                    $originalOrder = $originalStates[$id]['order_column'];
                    $originalStatus = $originalStates[$id]['status'];
                    $newOrder = $updatedTasks[$id]->order_column;
                    $newStatus = $updatedTasks[$id]->status;

                    logger()->debug('KANBAN ORDER/STATUS CHECK', [
                        'task_id' => $id,
                        'originalOrder' => $originalOrder,
                        'originalStatus' => $originalStatus,
                        'newOrder' => $newOrder,
                        'newStatus' => $newStatus,
                    ]);

                    // Only log activity if there's a meaningful change
                    if ($originalStatus != $newStatus) {
                        logger()->debug('KANBAN ABOUT TO LOG ACTIVITY - STATUS CHANGE', [
                            'task_id' => $id,
                            'originalStatus' => $originalStatus,
                            'newStatus' => $newStatus,
                            'user' => Auth::id(),
                        ]);

                        // Only log status changes, not order changes within the same column
                        $changes = [
                            'status' => [
                                'old' => $originalStatus,
                                'new' => $newStatus,
                            ],
                        ];

                        // Generate meaningful description
                        $description = "Task moved from {$originalStatus} to {$newStatus}";

                        try {
                            activity('Tasks')
                                ->performedOn($updatedTasks[$id])
                                ->causedBy(Auth::user())
                                ->withProperties([
                                    'status' => $newStatus,
                                    'changes' => $changes,
                                    'description' => $description,
                                ])
                                ->event('Task Moved')
                                ->log($description);
                        } catch (\Throwable $e) {
                            // Fallback: try logging without causedBy
                            logger('KANBAN ACTIVITYLOG ERROR', [
                                'error' => $e->getMessage(),
                                'task_id' => $id,
                                'changes' => $changes,
                            ]);
                            try {
                                activity('Tasks')
                                    ->performedOn($updatedTasks[$id])
                                    ->withProperties([
                                        'status' => $newStatus,
                                        'changes' => $changes,
                                        'description' => $description,
                                    ])
                                    ->event('Task Moved')
                                    ->log($description);
                            } catch (\Throwable $e2) {
                                logger('KANBAN ACTIVITYLOG FATAL', [
                                    'error' => $e2->getMessage(),
                                    'task_id' => $id,
                                    'changes' => $changes,
                                ]);
                            }
                        }
                    } else {
                        logger()->debug('KANBAN NO STATUS CHANGE - SKIPPING LOG', [
                            'task_id' => $id,
                            'status' => $originalStatus,
                            'orderChanged' => $originalOrder != $newOrder,
                        ]);
                    }
                }
            }
            $this->refreshBoard();
            $this->dispatch('kanban-order-updated', [
                'column' => $columnId,
                'cardIds' => $cardIds,
            ]);
            // Dispatch task-moved event for badge updates
            $this->dispatch('task-moved');
        }

        return $success;
    }
}
