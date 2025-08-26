<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use App\Http\Resources\TaskApiResource;

class TaskController extends Controller
{
  public function index(Request $request)
  {
    $withRelations = ['updatedBy'];
    // Load project relation only if it exists on the Task model
    if (method_exists(Task::class, 'project')) {
      $withRelations[] = 'project';
    }
    $query = Task::query()
      ->with($withRelations);

    $limit = (int) $request->input('limit', 50);
    $tasks = $query->limit($limit)->get();

    // Use API Resource to shape the output
    return response()->json(['tasks' => TaskApiResource::collection($tasks)]);
  }
}
