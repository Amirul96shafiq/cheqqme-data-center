<?php

namespace App\Observers;

use App\Models\Task;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

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
  }
}
