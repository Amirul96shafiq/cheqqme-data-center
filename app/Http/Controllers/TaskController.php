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
            $withRelations = ['updatedBy'];
            if (method_exists(Task::class, 'project')) {
                $withRelations[] = 'project';
            }
            
            $query = Task::query()->with($withRelations);
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
