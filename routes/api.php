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
use App\Http\Controllers\TrelloBoardController;
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
    Route::get('/trello-boards', [TrelloBoardController::class, 'index'])->name('api.trello-boards');
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
                'GET /projects' => 'Get user projects with pagination',
                'GET /api-key-info' => 'Get API key information',

                // Resource endpoints
                'GET /clients' => 'Get all clients with search, filtering, and sorting',
                'GET /tasks' => 'Get all tasks with search, filtering, sorting, and ID search',
                'GET /trello-boards' => 'Get all Trello boards with search, filtering, sorting, and ID search',
                'GET /projects' => 'Get all projects with search, filtering, sorting, and ID search',
                'GET /documents' => 'Get all documents with search, filtering, sorting, and ID search',
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
            'trello_board_api_examples' => [
                'GET /api/trello-boards?id=1' => 'Get Trello board by exact ID',
                'GET /api/trello-boards?search=1' => 'Search Trello boards by ID (numeric search)',
                'GET /api/trello-boards?search=project' => 'Search Trello boards by name, URL, or notes',
                'GET /api/trello-boards?show_on_boards=true' => 'Filter Trello boards shown on navigation',
                'GET /api/trello-boards?created_by=1' => 'Filter Trello boards by creator ID',
                'GET /api/trello-boards?updated_by=1' => 'Filter Trello boards by last updater ID',
                'GET /api/trello-boards?sort_by=name&sort_order=asc' => 'Sort Trello boards by name ascending',
                'GET /api/trello-boards?limit=10' => 'Limit results to 10 Trello boards',
                'GET /api/trello-boards?id=1&limit=5' => 'Get Trello board by ID with limit (ID takes priority)',
            ],
            'project_api_examples' => [
                'GET /api/projects?id=1' => 'Get project by exact ID',
                'GET /api/projects?search=1' => 'Search projects by ID (numeric search)',
                'GET /api/projects?search=Project' => 'Search projects by title, description, status, or notes',
                'GET /api/projects?status=Completed' => 'Filter projects by status',
                'GET /api/projects?client_id=23' => 'Filter projects by client ID',
                'GET /api/projects?updated_by=7' => 'Filter projects by last updater ID',
                'GET /api/projects?has_documents=true' => 'Filter projects that have documents',
                'GET /api/projects?has_important_urls=true' => 'Filter projects that have important URLs',
                'GET /api/projects?sort_by=title&sort_order=asc' => 'Sort projects by title ascending',
                'GET /api/projects?limit=10' => 'Limit results to 10 projects',
                'GET /api/projects?id=17&limit=5' => 'Get project by ID with limit (ID takes priority)',
            ],
            'document_api_examples' => [
                'GET /api/documents?id=1' => 'Get document by exact ID',
                'GET /api/documents?search=1' => 'Search documents by ID (numeric search)',
                'GET /api/documents?search=external' => 'Search documents by title, type, URL, or notes',
                'GET /api/documents?type=external' => 'Filter documents by type',
                'GET /api/documents?project_id=17' => 'Filter documents by project ID',
                'GET /api/documents?updated_by=7' => 'Filter documents by last updater ID',
                'GET /api/documents?has_file_path=true' => 'Filter documents that have file paths',
                'GET /api/documents?has_url=true' => 'Filter documents that have URLs',
                'GET /api/documents?sort_by=title&sort_order=asc' => 'Sort documents by title ascending',
                'GET /api/documents?limit=10' => 'Limit results to 10 documents',
                'GET /api/documents?id=1&limit=5' => 'Get document by ID with limit (ID takes priority)',
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
    Route::get('/trello-boards', [TrelloBoardController::class, 'index'])->name('api.trello-boards');
});
