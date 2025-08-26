<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\OpenaiLogController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ImportantUrlController;
use App\Http\Controllers\PhoneNumberController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

// OpenAI logs API endpoints protected by Sanctum
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/openai-logs', [OpenaiLogController::class, 'apiIndex'])->name('api.openai.logs');
    Route::get('/clients', [ClientController::class, 'index'])->name('api.clients');
    Route::get('/projects', [ProjectController::class, 'index'])->name('api.projects');
    Route::get('/documents', [DocumentController::class, 'index'])->name('api.documents');
    Route::get('/important-urls', [ImportantUrlController::class, 'index'])->name('api.important-urls');
    Route::get('/phone-numbers', [PhoneNumberController::class, 'index'])->name('api.phone-numbers');
    Route::get('/users', [UserController::class, 'index'])->name('api.users');
    Route::get('/tasks', [TaskController::class, 'index'])->name('api.tasks');
    Route::get('/comments', [CommentController::class, 'index'])->name('api.comments');
    Route::get('/comments/{comment}', [CommentController::class, 'show'])->name('api.comments.show');
});
