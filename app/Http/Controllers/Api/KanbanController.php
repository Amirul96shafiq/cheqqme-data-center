<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KanbanController extends Controller
{
    /**
     * Update the order of cards in a column (for pure Alpine.js drag and drop)
     */
    public function updateOrder(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'columnId' => 'required|string',
                'cardIds' => 'required|array',
                'cardIds.*' => 'integer',
            ]);

            $columnId = $request->columnId;
            $cardIds = $request->cardIds;

            Log::debug('API KANBAN UPDATE ORDER', [
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

            // Update the records using the same logic as the Livewire component
            DB::transaction(function () use ($columnId, $cardIds) {
                foreach ($cardIds as $order => $taskId) {
                    Task::where('id', $taskId)->update([
                        'order_column' => $order + 1,
                        'status' => $columnId,
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
                }
            });

            // Re-fetch tasks after update for logging
            $updatedTasks = Task::whereIn('id', $cardIds)->get()->keyBy('id');

            foreach ($cardIds as $order => $id) {
                if (isset($originalStates[$id]) && isset($updatedTasks[$id])) {
                    $originalOrder = $originalStates[$id]['order_column'];
                    $originalStatus = $originalStates[$id]['status'];
                    $newOrder = $updatedTasks[$id]->order_column;
                    $newStatus = $updatedTasks[$id]->status;

                    if ($originalOrder != $newOrder || $originalStatus != $newStatus) {
                        // Log activity for task movement with translation support
                        activity()
                            ->performedOn($updatedTasks[$id])
                            ->causedBy(Auth::user())
                            ->event(__('task.activity_log.activity_task_moved'))
                            ->withProperties([
                                'old_status' => $originalStatus,
                                'new_status' => $newStatus,
                                'old_order' => $originalOrder,
                                'new_order' => $newOrder,
                                'via' => 'kanban_drag_drop',
                            ])
                            ->log(__('task.activity_log.activity_task_moved').' from '.$originalStatus.' to '.$newStatus);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Cards reordered successfully',
                'data' => [
                    'columnId' => $columnId,
                    'cardIds' => $cardIds,
                    'timestamp' => now()->toISOString(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('API KANBAN UPDATE ORDER ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update card order: '.$e->getMessage(),
            ], 500);
        }
    }
}
