<?php

use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ImportantUrlController;
use App\Http\Controllers\OpenaiLogController;
use App\Http\Controllers\PhoneNumberController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// API endpoints protected by Sanctum
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

// Public API documentation endpoint with pretty JSON
Route::get('/documentation', function () {
    return response()->json([
        'success' => true,
        'message' => 'API Documentation',
        'data' => [
            'base_url' => config('app.url') . '/api',
            'authentication' => 'Bearer token in Authorization header',
            'features' => [
                'pretty_json' => 'All responses are formatted with proper indentation using JSON_PRETTY_PRINT',
                'consistent_format' => 'Standardized response structure across all endpoints',
                'request_tracking' => 'Unique request IDs for better debugging and monitoring',
            ],
            'endpoints' => [
                // User endpoints
                'GET /profile' => 'Get user profile information',
                'GET /tasks' => 'Get user tasks with pagination',
                'GET /projects' => 'Get user projects with pagination',
                'GET /api-key-info' => 'Get API key information',

                // Resource endpoints
                'GET /clients' => 'Get all clients with search, filtering, and sorting',
                'GET /tasks' => 'Get all tasks with search, filtering, sorting, and ID search',
                'GET /documents' => 'Get all documents',
                'GET /important-urls' => 'Get all important URLs',
                'GET /phone-numbers' => 'Get all phone numbers',
                'GET /users' => 'Get all users',
                'GET /comments' => 'Get all comments',
                'GET /comments/{comment}' => 'Get specific comment by ID',
            ],
            'example_request' => 'GET ' . config('app.url') . '/api/profile',
            'headers' => [
                'Authorization' => 'Bearer YOUR_API_KEY',
                'Accept' => 'application/json',
                'X-Request-ID' => 'Optional: Custom request ID for tracking',
            ],
            'task_api_examples' => [
                'GET /api/tasks?id=1' => 'Get task by exact ID',
                'GET /api/tasks?search=1' => 'Search tasks by ID (numeric search)',
                'GET /api/tasks?search=voluptas' => 'Search tasks by title, description, or status',
                'GET /api/tasks?status=archived' => 'Filter tasks by status',
                'GET /api/tasks?assigned_to=4' => 'Filter tasks by assigned user ID',
                'GET /api/tasks?due_date_from=2025-09-01' => 'Filter tasks due after date',
                'GET /api/tasks?due_date_to=2025-09-30' => 'Filter tasks due before date',
                'GET /api/tasks?sort_by=title&sort_order=asc' => 'Sort tasks by title ascending',
                'GET /api/tasks?limit=10' => 'Limit results to 10 tasks',
                'GET /api/tasks?id=1&limit=5' => 'Get task by ID with limit (ID takes priority)',
            ],
            'response_format' => [
                'success' => 'boolean',
                'message' => 'string',
                'data' => 'mixed',
                'meta' => [
                    'timestamp' => 'ISO 8601 timestamp',
                    'request_id' => 'unique request identifier (UUID)',
                ]
            ],
        ],
    ], 200, [], JSON_PRETTY_PRINT);
})->name('api.documentation');

// API Key authenticated endpoints
Route::middleware([\App\Http\Middleware\ApiKeyAuth::class])->group(function () {
    // User endpoints
    Route::get('/profile', [ApiUserController::class, 'profile'])->name('api.user.profile');
    // Route::get('/tasks', [ApiUserController::class, 'tasks'])->name('api.user.tasks');
    Route::get('/tasks', [TaskController::class, 'index'])->name('api.tasks');
    // Route::get('/projects', [ApiUserController::class, 'projects'])->name('api.user.projects');
    Route::get('/projects', [ProjectController::class, 'index'])->name('api.projects');
    Route::get('/api-key-info', [ApiUserController::class, 'apiKeyInfo'])->name('api.user.api-key-info');

    // Resource endpoints
    Route::get('/clients', [ClientController::class, 'index'])->name('api.clients');
    Route::get('/documents', [DocumentController::class, 'index'])->name('api.documents');
    Route::get('/important-urls', [ImportantUrlController::class, 'index'])->name('api.important-urls');
    Route::get('/phone-numbers', [PhoneNumberController::class, 'index'])->name('api.phone-numbers');
    Route::get('/users', [UserController::class, 'index'])->name('api.users');
    Route::get('/comments', [CommentController::class, 'index'])->name('api.comments');
    Route::get('/comments/{comment}', [CommentController::class, 'show'])->name('api.comments.show');
});
