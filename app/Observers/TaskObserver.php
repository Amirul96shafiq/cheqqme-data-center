<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class TaskObserver
{
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
                ->outlined()
            ])
            ->sendToDatabase($assignedUser);
        }
      }
    }
  }
}
