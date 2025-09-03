<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use App\Services\TaskCountService;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class TaskObserver
{
    public function __construct(
        private TaskCountService $taskCountService
    ) {
    }

    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task)
    {
        // Send notification to assigned user when task is created
        if ($task->assigned_to) {
            $assignedUser = User::find($task->assigned_to);
            if ($assignedUser) {
                Notification::make()
                    ->title(__('task.notifications.assigned_title'))
                    ->body(__('task.notifications.assigned_body', ['task' => $task->title]))
                    ->icon('heroicon-o-user-plus')
                    ->success()
                    ->actions([
                        Action::make('view_task')
                            ->label(__('task.notifications.view_task'))
                            ->url(route('filament.admin.resources.tasks.edit', $task))
                            ->button()
                            ->outlined(),
                    ])
                    ->sendToDatabase($assignedUser);
            }
        }

        // Broadcast task count updates
        $this->taskCountService->broadcastTaskCountUpdatesForTask($task);
    }

    /**
     * Handle the Task "updating" event.
     */
    public function updating(Task $task)
    {
        if ($task->isDirty('order_column') || $task->isDirty('status')) {
            // Temporarily disable activity logging for this update
            $task->disableLogging();

            activity('Tasks')
                ->performedOn($task)
                ->causedBy(auth()->user())
                ->event('Task Moved')
                ->withProperties([
                    'old_status' => $task->getOriginal('status'),
                    'new_status' => $task->status,
                    'old_order_column' => $task->getOriginal('order_column'),
                    'new_order_column' => $task->order_column,
                ])
                ->log('Task Moved');
        }

        // Handle assignment notifications
        if ($task->isDirty('assigned_to')) {
            $oldAssignedUserId = $task->getOriginal('assigned_to');
            $newAssignedUserId = $task->assigned_to;

            // Send notification to newly assigned user
            if ($newAssignedUserId && $newAssignedUserId !== $oldAssignedUserId) {
                $assignedUser = User::find($newAssignedUserId);
                if ($assignedUser) {
                    Notification::make()
                        ->title(__('task.notifications.assigned_title'))
                        ->body(__('task.notifications.assigned_body', ['task' => $task->title]))
                        ->icon('heroicon-o-user-plus')
                        ->success()
                        ->actions([
                            Action::make('view_task')
                                ->label(__('task.notifications.view_task'))
                                ->url(route('filament.admin.resources.tasks.edit', $task))
                                ->button()
                                ->outlined(),
                        ])
                        ->sendToDatabase($assignedUser);
                }
            }
        }
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task)
    {
        // Broadcast task count updates for both old and new assigned users
        $this->taskCountService->broadcastTaskCountUpdatesForTask($task);

        // If assignment changed, also broadcast for the old assigned user
        if ($task->isDirty('assigned_to')) {
            $oldAssignedUserId = $task->getOriginal('assigned_to');
            if ($oldAssignedUserId && is_numeric($oldAssignedUserId)) {
                $this->taskCountService->broadcastTaskCountUpdate((int) $oldAssignedUserId);
            }
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task)
    {
        // Broadcast task count updates for assigned users
        $this->taskCountService->broadcastTaskCountUpdatesForTask($task);
    }
}
