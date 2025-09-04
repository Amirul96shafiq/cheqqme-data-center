<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Task;

Route::get('/test-mentions', function () {
    $user = User::first();
    $task = Task::first();
    
    if (!$user || !$task) {
        return response()->json([
            'error' => 'No user or task found. Please ensure you have test data.',
            'users_count' => User::count(),
            'tasks_count' => Task::count(),
        ]);
    }
    
    return view('test-mentions', compact('user', 'task'));
});
