<?php

declare(strict_types=1);

namespace App\Http\Livewire\Relaticle\Flowforge;

use Relaticle\Flowforge\Livewire\KanbanBoard as BaseKanbanBoard;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class KanbanBoard extends BaseKanbanBoard
{
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

          if ($originalOrder != $newOrder || $originalStatus != $newStatus) {
            logger()->debug('KANBAN ABOUT TO LOG ACTIVITY', [
              'task_id' => $id,
              'newOrder' => $newOrder,
              'newStatus' => $newStatus,
              'user' => Auth::id(),
            ]);
            $changes = [
              'order_column' => [
                'old' => $originalOrder,
                'new' => $newOrder,
              ],
              'status' => [
                'old' => $originalStatus,
                'new' => $newStatus,
              ],
            ];
            try {
              activity('Tasks')
                ->performedOn($updatedTasks[$id])
                ->causedBy(Auth::user())
                ->withProperties([
                  'order_column' => $newOrder,
                  'status' => $newStatus,
                  'changes' => $changes,
                ])
                ->event('Task Moved')
                ->log('Task moved');
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
                    'order_column' => $newOrder,
                    'status' => $newStatus,
                    'changes' => $changes,
                  ])
                  ->event('Task Moved')
                  ->log('Task moved (no user)');
              } catch (\Throwable $e2) {
                logger('KANBAN ACTIVITYLOG FATAL', [
                  'error' => $e2->getMessage(),
                  'task_id' => $id,
                  'changes' => $changes,
                ]);
              }
            }
          }
        }
      }
      $this->refreshBoard();
      $this->dispatch('kanban-order-updated', [
        'column' => $columnId,
        'cardIds' => $cardIds,
      ]);
    }

    return $success;
  }
}
