<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskApiResource;
use App\Models\Task;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $withRelations = ['updatedBy', 'assignedTo'];
            if (method_exists(Task::class, 'project')) {
                $withRelations[] = 'project';
            }

            $query = Task::query()->with($withRelations);

            // Add exact ID search (highest priority)
            if ($request->filled('id')) {
                $id = $request->input('id');
                $query->where('id', $id);
            }
            // Add search functionality (if no specific ID is provided)
            elseif ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    // Check if search is numeric (could be an ID)
                    if (is_numeric($search)) {
                        $q->where('id', $search)
                            ->orWhere('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%");
                    } else {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%");
                    }
                });
            }

            // Add filtering by status
            if ($request->filled('status')) {
                $status = $request->input('status');
                $query->where('status', $status);
            }

            // Add filtering by assigned_to
            if ($request->filled('assigned_to')) {
                $assignedTo = $request->input('assigned_to');
                $query->where('assigned_to', $assignedTo);
            }

            // Add filtering by due date
            if ($request->filled('due_date_from')) {
                $dueDateFrom = $request->input('due_date_from');
                $query->where('due_date', '>=', $dueDateFrom);
            }

            if ($request->filled('due_date_to')) {
                $dueDateTo = $request->input('due_date_to');
                $query->where('due_date', '<=', $dueDateTo);
            }

            // Add sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $limit = (int) $request->input('limit', 50);
            $tasks = $query->limit($limit)->get();

            return $this->successResponse(
                TaskApiResource::collection($tasks),
                'Tasks retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve tasks',
                500,
                ['error' => $e->getMessage()]
            );
        }
    }
}
