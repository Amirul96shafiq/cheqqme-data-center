<?php

namespace App\Observers;

use App\Models\Task;
use Illuminate\Support\Facades\Log;

class TaskObserver
{
  /**
   * Handle the Task "updating" event.
   */
  public function updating(Task $task)
  {
    // If order_column or status is dirty, force activity log
    if ($task->isDirty('order_column') || $task->isDirty('status')) {
      // Touch updated_at to trigger activitylog
      $task->updated_at = now();
    }
  }
}
