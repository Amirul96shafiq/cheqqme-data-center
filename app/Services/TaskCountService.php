<?php

namespace App\Services;

use App\Events\TaskCountUpdated;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskCountService
{
    /**
     * Get the count of active tasks assigned to a user
     */
    public function getActiveTaskCount(int $userId): int
    {
        return Task::query()
            ->where('assigned_to', 'like', '%'.$userId.'%')
            ->whereIn('status', ['todo', 'in_progress', 'toreview', 'issue_tracker'])
            ->count();
    }

    /**
     * Broadcast task count update for a specific user
     */
    public function broadcastTaskCountUpdate(int $userId): void
    {
        $count = $this->getActiveTaskCount($userId);
        TaskCountUpdated::dispatch($userId, $count);
    }

    /**
     * Broadcast task count update for the current authenticated user
     */
    public function broadcastCurrentUserTaskCountUpdate(): void
    {
        if (Auth::check()) {
            $this->broadcastTaskCountUpdate(Auth::id());
        }
    }

    /**
     * Broadcast task count updates for all users assigned to a task
     */
    public function broadcastTaskCountUpdatesForTask(Task $task): void
    {
        if (! $task->assigned_to) {
            return;
        }

        $userIds = is_array($task->assigned_to) ? $task->assigned_to : [$task->assigned_to];

        foreach ($userIds as $userId) {
            if (is_numeric($userId)) {
                $this->broadcastTaskCountUpdate((int) $userId);
            }
        }
    }
}
