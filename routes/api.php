<?php

use App\Http\Controllers\Api\PlaywrightMcpController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentReactionController;
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
    Route::get('/clients', [ClientController::class, 'index'])->name('api.clients');
    Route::get('/projects', [ProjectController::class, 'index'])->name('api.projects');
    Route::get('/documents', [DocumentController::class, 'index'])->name('api.documents');
    Route::get('/important-urls', [ImportantUrlController::class, 'index'])->name('api.important-urls');
    Route::get('/phone-numbers', [PhoneNumberController::class, 'index'])->name('api.phone-numbers');
    Route::get('/users', [UserController::class, 'index'])->name('api.users');
    Route::get('/tasks', [TaskController::class, 'index'])->name('api.tasks');
    Route::get('/comments', [CommentController::class, 'index'])->name('api.comments');
    Route::get('/comments/{comment}', [CommentController::class, 'show'])->name('api.comments.show');
    Route::post('/comments', [CommentController::class, 'store'])->name('api.comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('api.comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('api.comments.destroy');

    // Comment reactions
    Route::get('/comments/{comment}/reactions', [CommentReactionController::class, 'index'])->name('api.comment-reactions.index');
    Route::post('/comment-reactions', [CommentReactionController::class, 'store'])->name('api.comment-reactions.store');
    Route::delete('/comments/{comment}/reactions', [CommentReactionController::class, 'destroy'])->name('api.comment-reactions.destroy');

    Route::get('/trello-boards', [TrelloBoardController::class, 'index'])->name('api.trello-boards');
    Route::get('/openai-logs', [OpenaiLogController::class, 'apiIndex'])->name('api.openai.logs');

    // Playwright MCP integration endpoints
    Route::prefix('playwright')->group(function () {
        Route::get('/status', [PlaywrightMcpController::class, 'status'])->name('api.playwright.status');
        Route::post('/screenshot', [PlaywrightMcpController::class, 'screenshot'])->name('api.playwright.screenshot');
        Route::post('/test-url', [PlaywrightMcpController::class, 'testUrl'])->name('api.playwright.test-url');
        Route::post('/extract-data', [PlaywrightMcpController::class, 'extractData'])->name('api.playwright.extract-data');
        Route::post('/test-filament', [PlaywrightMcpController::class, 'testFilamentPanel'])->name('api.playwright.test-filament');
        Route::post('/test-action-board', [PlaywrightMcpController::class, 'testActionBoard'])->name('api.playwright.test-action-board');
        Route::post('/test-api', [PlaywrightMcpController::class, 'testApiEndpoint'])->name('api.playwright.test-api');
        Route::post('/test-report', [PlaywrightMcpController::class, 'generateTestReport'])->name('api.playwright.test-report');
        Route::post('/test-boost-integration', [PlaywrightMcpController::class, 'testBoostIntegration'])->name('api.playwright.test-boost-integration');
    });
});

// Public API documentation endpoint with pretty JSON
Route::get('/documentation', function () {
    return response()->json([
        'success' => true,
        'message' => 'API Documentation',
        'data' => [
            'base_url' => config('app.url').'/api',
            'authentication' => 'Bearer token in Authorization header',
            'features' => [
                'pretty_json' => 'All responses are formatted with proper indentation using JSON_PRETTY_PRINT',
                'consistent_format' => 'Standardized response structure across all endpoints',
                'request_tracking' => 'Unique request IDs for better debugging and monitoring',
            ],
            'endpoints' => [
                // User endpoints
                'GET /profile' => 'Get user profile information',
                'GET /api-key-info' => 'Get API key information',

                // Resource endpoints
                'GET /clients' => 'Get all clients with search, filtering, and sorting',
                'GET /tasks' => 'Get all tasks with search, filtering, sorting, and ID search',
                'GET /trello-boards' => 'Get all Trello boards with search, filtering, sorting, and ID search',
                'GET /projects' => 'Get all projects with search, filtering, sorting, and ID search',
                'GET /documents' => 'Get all documents with search, filtering, sorting, and ID search',
                'GET /important-urls' => 'Get all important URLs with search, filtering, sorting, and ID search',
                'GET /phone-numbers' => 'Get all phone numbers with search, filtering, sorting, and ID search',
                'GET /users' => 'Get all users with search, filtering, sorting, and ID search',
                'GET /comments' => 'Get all comments with search, filtering, sorting, and ID search',
                'GET /comments/{comment}' => 'Get specific comment by ID',
            ],
            'example_request' => 'GET '.config('app.url').'/api/profile',
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
            'important_url_api_examples' => [
                'GET /api/important-urls?id=1' => 'Get important URL by exact ID',
                'GET /api/important-urls?search=1' => 'Search important URLs by ID (numeric search)',
                'GET /api/important-urls?search=IMPORTANT' => 'Search important URLs by title, URL, or notes',
                'GET /api/important-urls?project_id=17' => 'Filter important URLs by project ID',
                'GET /api/important-urls?client_id=23' => 'Filter important URLs by client ID',
                'GET /api/important-urls?updated_by=7' => 'Filter important URLs by last updater ID',
                'GET /api/important-urls?has_project=true' => 'Filter important URLs that have associated projects',
                'GET /api/important-urls?has_client=true' => 'Filter important URLs that have associated clients',
                'GET /api/important-urls?sort_by=title&sort_order=asc' => 'Sort important URLs by title ascending',
                'GET /api/important-urls?limit=10' => 'Limit results to 10 important URLs',
                'GET /api/important-urls?id=1&limit=5' => 'Get important URL by ID with limit (ID takes priority)',
            ],
            'phone_number_api_examples' => [
                'GET /api/phone-numbers?id=1' => 'Get phone number by exact ID',
                'GET /api/phone-numbers?search=1' => 'Search phone numbers by ID (numeric search)',
                'GET /api/phone-numbers?search=Support' => 'Search phone numbers by title, phone, or notes',
                'GET /api/phone-numbers?phone=+62' => 'Filter phone numbers by phone number pattern',
                'GET /api/phone-numbers?updated_by=1' => 'Filter phone numbers by last updater ID',
                'GET /api/phone-numbers?has_notes=true' => 'Filter phone numbers that have notes',
                'GET /api/phone-numbers?phone_format=international' => 'Filter phone numbers by format (international, mobile, landline)',
                'GET /api/phone-numbers?phone_format=mobile' => 'Filter mobile phone numbers',
                'GET /api/phone-numbers?phone_format=landline' => 'Filter landline phone numbers',
                'GET /api/phone-numbers?sort_by=title&sort_order=asc' => 'Sort phone numbers by title ascending',
                'GET /api/phone-numbers?limit=10' => 'Limit results to 10 phone numbers',
                'GET /api/phone-numbers?id=1&limit=5' => 'Get phone number by ID with limit (ID takes priority)',
            ],
            'user_api_examples' => [
                'GET /api/users?id=1' => 'Get user by exact ID',
                'GET /api/users?search=1' => 'Search users by ID (numeric search)',
                'GET /api/users?search=Test' => 'Search users by name, username, or email',
                'GET /api/users?username=test' => 'Filter users by username pattern',
                'GET /api/users?email=test@example.com' => 'Filter users by email pattern',
                'GET /api/users?updated_by=1' => 'Filter users by last updater ID',
                'GET /api/users?has_api_key=true' => 'Filter users that have API keys',
                'GET /api/users?email_verified=true' => 'Filter users with verified emails',
                'GET /api/users?has_avatar=true' => 'Filter users that have avatars',
                'GET /api/users?has_cover_image=true' => 'Filter users that have cover images',
                'GET /api/users?timezone=Asia/Kuala_Lumpur' => 'Filter users by timezone',
                'GET /api/users?sort_by=name&sort_order=asc' => 'Sort users by name ascending',
                'GET /api/users?limit=10' => 'Limit results to 10 users',
                'GET /api/users?id=1&limit=5' => 'Get user by ID with limit (ID takes priority)',
            ],
            'comment_api_examples' => [
                'GET /api/comments?id=1' => 'Get comment by exact ID',
                'GET /api/comments?search=1' => 'Search comments by ID (numeric search)',
                'GET /api/comments?search=hehe' => 'Search comments by comment text',
                'GET /api/comments?task_id=1' => 'Filter comments by task ID',
                'GET /api/comments?user_id=1' => 'Filter comments by user ID',
                'GET /api/comments?has_mentions=true' => 'Filter comments that have mentions',
                'GET /api/comments?mention_user_id=7' => 'Filter comments that mention a specific user',
                'GET /api/comments?comment_length=short' => 'Filter comments by length (short, medium, long)',
                'GET /api/comments?comment_length=medium' => 'Filter medium-length comments',
                'GET /api/comments?comment_length=long' => 'Filter long comments',
                'GET /api/comments?created_after=2025-09-01' => 'Filter comments created after date',
                'GET /api/comments?created_before=2025-09-03' => 'Filter comments created before date',
                'GET /api/comments?updated_by=1' => 'Filter comments by last updater ID',
                'GET /api/comments?sort_by=created_at&sort_order=desc' => 'Sort comments by creation date descending',
                'GET /api/comments?limit=10' => 'Limit results to 10 comments',
                'GET /api/comments?id=1&limit=5' => 'Get comment by ID with limit (ID takes priority)',
            ],
            'response_format' => [
                'success' => 'boolean',
                'message' => 'string',
                'data' => 'mixed',
                'meta' => [
                    'timestamp' => 'ISO 8601 timestamp',
                    'request_id' => 'unique request identifier (UUID)',
                ],
            ],
        ],
    ], 200, [], JSON_PRETTY_PRINT);
})->name('api.documentation');

// API Key authenticated endpoints
Route::middleware([\App\Http\Middleware\ApiKeyAuth::class])->group(function () {
    // User endpoints
    Route::get('/profile', [ApiUserController::class, 'profile'])->name('api.user.profile');
    Route::get('/api-key-info', [ApiUserController::class, 'apiKeyInfo'])->name('api.user.api-key-info');

    // Resource endpoints
    Route::get('/clients', [ClientController::class, 'index'])->name('api.clients');
    Route::get('/documents', [DocumentController::class, 'index'])->name('api.documents');
    Route::get('/projects', [ProjectController::class, 'index'])->name('api.projects');
    Route::get('/important-urls', [ImportantUrlController::class, 'index'])->name('api.important-urls');
    Route::get('/phone-numbers', [PhoneNumberController::class, 'index'])->name('api.phone-numbers');
    Route::get('/users', [UserController::class, 'index'])->name('api.users');
    Route::get('/tasks', [TaskController::class, 'index'])->name('api.tasks');
    Route::get('/comments', [CommentController::class, 'index'])->name('api.comments');
    Route::get('/comments/{comment}', [CommentController::class, 'show'])->name('api.comments.show');
    Route::get('/trello-boards', [TrelloBoardController::class, 'index'])->name('api.trello-boards');
});
