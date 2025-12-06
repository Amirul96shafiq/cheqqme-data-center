<?php

namespace App\Services;

use App\Filament\Resources\TaskResource;
use App\Models\Client;
use App\Models\Document;
use App\Models\ImportantUrl;
use App\Models\PhoneNumber;
use App\Models\Project;
use App\Models\Task;
use App\Models\TrelloBoard;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatbotService
{
    /**
     * The tools available to the chatbot
     *
     * @var array<string, callable>
     */
    protected array $tools = [];

    /**
     * The user the chatbot is interacting with
     */
    protected User $user;

    /**
     * The constructor for the ChatbotService
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->registerTools();
    }

    /**
     * Calculate active and trashed counts for a model with optional constraints.
     *
     * @param  class-string<Model>  $modelClass
     */
    protected function calculateModelCounts(string $modelClass, ?callable $constraint = null): array
    {
        $query = $modelClass::query();

        if ($constraint) {
            $query = $constraint($query);
        }

        $activeCount = (clone $query)->count();

        $usesSoftDeletes = in_array(SoftDeletes::class, class_uses_recursive($modelClass));
        $trashedCount = 0;

        if ($usesSoftDeletes) {
            $trashedCount = (clone $query)->onlyTrashed()->count();
        }

        return [
            'active' => $activeCount,
            'trashed' => $trashedCount,
            'total' => $activeCount + $trashedCount,
        ];
    }

    /**
     * Register the tools available to the chatbot
     */
    protected function registerTools(): void
    {
        $this->tools = [
            'show_help' => [$this, 'showHelp'], // Show all available shortcuts and commands. Shortcut: /help
            'get_incomplete_tasks' => [$this, 'getIncompleteTasks'], // Get incomplete tasks with count and/or detailed breakdown by status with URLs. Shortcut: /mytask
            'get_client_urls' => [$this, 'getClientUrls'], // Get URLs for client management (create new, list all) with total count. Shortcut: /client
            'get_project_urls' => [$this, 'getProjectUrls'], // Get URLs for project management (create new, list all) with total count. Shortcut: /project
            'get_document_urls' => [$this, 'getDocumentUrls'], // Get URLs for document management (create new, list all) with total count. Shortcut: /document
            'get_important_url_urls' => [$this, 'getImportantUrlUrls'], // Get URLs for important URL management (create new, list all) with total count. Shortcut: /important-url
            'get_phone_number_urls' => [$this, 'getPhoneNumberUrls'], // Get URLs for phone number management (create new, list all) with total count. Shortcut: /phone-number
            'get_user_urls' => [$this, 'getUserUrls'], // Get URLs for user management (create new, list all) with total count. Shortcut: /user
            'get_resource_counts' => [$this, 'getResourceCounts'], // Get total counts for all resources. Shortcut: /resources
            'get_trello_board_urls' => [$this, 'getTrelloBoardUrls'], // Get URLs for Trello board management (create new, list all) with total count. Shortcut: /trello-board
        ];
    }

    /**
     * Get the definition of a tool
     */
    public function getToolDefinition(string $name): ?array
    {
        $definitions = [
            'get_incomplete_tasks' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_incomplete_tasks',
                    'description' => 'MUST be called when user types /mytask or /mytask . Get incomplete tasks assigned to the current user. Respond in the user\'s language. Can return just the count, detailed breakdown by status, or both. Shortcut: /mytask',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'include_details' => [
                                'type' => 'boolean',
                                'description' => 'Whether to include detailed task breakdown by status with URLs. Default is true.',
                            ],
                            'include_count' => [
                                'type' => 'boolean',
                                'description' => 'Whether to include the total count of incomplete tasks. Default is true.',
                            ],
                        ],
                        'required' => [],
                    ],
                ],
            ],
            // 'get_task_url_by_name' removed: task searching available via header search
            'show_help' => [
                'type' => 'function',
                'function' => [
                    'name' => 'show_help',
                    'description' => 'MUST be called when user types /help or /help . Show all available shortcuts and commands in the user\'s language. Respond in the same language as the conversation. Shortcut: /help',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                        'required' => [],
                    ],
                ],
            ],
            'get_client_urls' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_client_urls',
                    'description' => 'Get URLs for client management including create new client and list all clients with total count. Respond in the user\'s language. Shortcut: /client',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                        'required' => [],
                    ],
                ],
            ],
            'get_project_urls' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_project_urls',
                    'description' => 'Get URLs for project management including create new project and list all projects with total count. Respond in the user\'s language. Shortcut: /project',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                        'required' => [],
                    ],
                ],
            ],
            'get_document_urls' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_document_urls',
                    'description' => 'Get URLs for document management including create new document and list all documents with total count. Shortcut: /document',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                        'required' => [],
                    ],
                ],
            ],
            'get_important_url_urls' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_important_url_urls',
                    'description' => 'Get URLs for important URL management including create new important URL and list all important URLs with total count. Shortcut: /important-url',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                        'required' => [],
                    ],
                ],
            ],
            'get_phone_number_urls' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_phone_number_urls',
                    'description' => 'Get URLs for phone number management including create new phone number and list all phone numbers with total count. Shortcut: /phone-number',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                        'required' => [],
                    ],
                ],
            ],
            'get_user_urls' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_user_urls',
                    'description' => 'Get URLs for user management including create new user and list all users with total count. Shortcut: /user',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                        'required' => [],
                    ],
                ],
            ],
            'get_resource_counts' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_resource_counts',
                    'description' => 'Get total counts for all resources in the system. Respond in the user\'s language. Shortcut: /resources',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                        'required' => [],
                    ],
                ],
            ],
            'get_trello_board_urls' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_trello_board_urls',
                    'description' => 'Get URLs for Trello board management including create new Trello board and list all Trello boards with total count. Respond in the user\'s language. Shortcut: /trello-board',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object) [],
                        'required' => [],
                    ],
                ],
            ],
        ];

        return $definitions[$name] ?? null;
    }

    /**
     * Get all tool definitions
     */
    public function getAllToolDefinitions(): array
    {
        return array_map([$this, 'getToolDefinition'], array_keys($this->tools));
    }

    /**
     * Execute a tool
     */
    public function executeTool(string $toolName, array $arguments): ?string
    {
        if (isset($this->tools[$toolName])) {
            return call_user_func($this->tools[$toolName], ...array_values($arguments));
        }

        return null;
    }

    /**
     * Tool: Show all available shortcuts and commands.
     * Shortcut: /help
     */
    public function showHelp(): string
    {
        // Return help content in multiple languages - AI will choose based on conversation context
        $helpContent = [
            'malay' => [
                'title' => '**Pintasan dan Arahan yang Tersedia** 🤖',
                'intro' => 'Berikut adalah pintasan yang boleh anda gunakan untuk mengakses ciri-ciri dengan cepat:',
                'shortcuts' => [
                    '/help' => 'Tunjukkan mesej bantuan ini dengan semua pintasan yang tersedia',
                    '/mytask' => 'Dapatkan tugas yang belum selesai dengan pecahan terperinci mengikut status',
                    '/client' => 'Dapatkan URL untuk pengurusan pelanggan dengan jumlah keseluruhan',
                    '/project' => 'Dapatkan URL untuk pengurusan projek dengan jumlah keseluruhan',
                    '/document' => 'Dapatkan URL untuk pengurusan dokumen dengan jumlah keseluruhan',
                    '/important-url' => 'Dapatkan URL untuk pengurusan URL penting dengan jumlah keseluruhan',
                    '/phone-number' => 'Dapatkan URL untuk pengurusan nombor telefon dengan jumlah keseluruhan',
                    '/user' => 'Dapatkan URL untuk pengurusan pengguna dengan jumlah keseluruhan',
                    '/resources' => 'Dapatkan jumlah keseluruhan untuk semua sumber dalam sistem',
                    '/trello-board' => 'Dapatkan URL untuk pengurusan papan Trello dengan jumlah keseluruhan',
                ],
                'footer' => 'Hanya taip mana-mana pintasan ini dalam mesej anda untuk menggunakannya dengan cepat! 🚀',
            ],
            'indonesian' => [
                'title' => '**Pintasan dan Perintah yang Tersedia** 🤖',
                'intro' => 'Berikut adalah pintasan yang dapat Anda gunakan untuk mengakses fitur dengan cepat:',
                'shortcuts' => [
                    '/help' => 'Tampilkan pesan bantuan ini dengan semua pintasan yang tersedia',
                    '/mytask' => 'Dapatkan tugas yang belum selesai dengan rincian terperinci berdasarkan status',
                    '/client' => 'Dapatkan URL untuk manajemen klien dengan jumlah total',
                    '/project' => 'Dapatkan URL untuk manajemen proyek dengan jumlah total',
                    '/document' => 'Dapatkan URL untuk manajemen dokumen dengan jumlah total',
                    '/important-url' => 'Dapatkan URL untuk manajemen URL penting dengan jumlah total',
                    '/phone-number' => 'Dapatkan URL untuk manajemen nomor telepon dengan jumlah total',
                    '/user' => 'Dapatkan URL untuk manajemen pengguna dengan jumlah total',
                    '/resources' => 'Dapatkan jumlah total untuk semua sumber dalam sistem',
                    '/trello-board' => 'Dapatkan URL untuk manajemen papan Trello dengan jumlah total',
                ],
                'footer' => 'Cukup ketik salah satu pintasan ini dalam pesan Anda untuk menggunakannya dengan cepat! 🚀',
            ],
            'chinese' => [
                'title' => '**可用的快捷方式和命令** 🤖',
                'intro' => '以下是您可以用来快速访问功能的快捷方式：',
                'shortcuts' => [
                    '/help' => '显示此帮助消息和所有可用的快捷方式',
                    '/mytask' => '获取您未完成的任务，按状态详细分类',
                    '/client' => '获取客户管理URL和总数',
                    '/project' => '获取项目管理URL和总数',
                    '/document' => '获取文档管理URL和总数',
                    '/important-url' => '获取重要URL管理URL和总数',
                    '/phone-number' => '获取电话号码管理URL和总数',
                    '/user' => '获取用户管理URL和总数',
                    '/resources' => '获取系统中所有资源的总数',
                    '/trello-board' => '获取Trello看板管理URL和总数',
                ],
                'footer' => '只需在消息中输入这些快捷方式中的任何一个即可快速使用！🚀',
            ],
            'korean' => [
                'title' => '**사용 가능한 단축키 및 명령** 🤖',
                'intro' => '다음은 기능에 빠르게 액세스할 수 있는 단축키입니다:',
                'shortcuts' => [
                    '/help' => '모든 사용 가능한 단축키와 함께 이 도움말 메시지를 표시',
                    '/mytask' => '상태별 세부 분석과 함께 미완료 작업 가져오기',
                    '/client' => '총 수와 함께 클라이언트 관리 URL 가져오기',
                    '/project' => '총 수와 함께 프로젝트 관리 URL 가져오기',
                    '/document' => '총 수와 함께 문서 관리 URL 가져오기',
                    '/important-url' => '총 수와 함께 중요한 URL 관리 URL 가져오기',
                    '/phone-number' => '총 수와 함께 전화번호 관리 URL 가져오기',
                    '/user' => '총 수와 함께 사용자 관리 URL 가져오기',
                    '/resources' => '시스템의 모든 리소스 총 수 가져오기',
                    '/trello-board' => '총 수와 함께 트렐로 보드 관리 URL 가져오기',
                ],
                'footer' => '메시지에 이러한 단축키 중 하나를 입력하여 빠르게 사용하세요! 🚀',
            ],
            'japanese' => [
                'title' => '**利用可能なショートカットとコマンド** 🤖',
                'intro' => '以下は機能に素早くアクセスするためのショートカットです:',
                'shortcuts' => [
                    '/help' => '利用可能なすべてのショートカットと共にこのヘルプメッセージを表示',
                    '/mytask' => 'ステータス別の詳細分析と共に未完了タスクを取得',
                    '/client' => '総数と共にクライアント管理URLを取得',
                    '/project' => '総数と共にプロジェクト管理URLを取得',
                    '/document' => '総数と共にドキュメント管理URLを取得',
                    '/important-url' => '総数と共に重要なURL管理URLを取得',
                    '/phone-number' => '総数와 함께 전화번호 관리 URL 가져오기',
                    '/user' => '総数와 함께 사용자 관리 URL 가져오기',
                    '/resources' => '시스템의 모든 리소스 총 수 가져오기',
                    '/trello-board' => '総数와 함께 트렐로 보드 관리 URL 가져오기',
                ],
                'footer' => 'メッセージにこれらのショートカットのいずれかを入力して素早く使用してください！🚀',
            ],
            'english' => [
                'title' => '**Available Shortcuts and Commands** 🤖',
                'intro' => 'Here are the shortcuts you can use to quickly access features:',
                'shortcuts' => [
                    '/help' => 'Show this help message with all available shortcuts',
                    '/mytask' => 'Get your incomplete tasks with detailed breakdown by status',
                    '/client' => 'Get URLs for client management with total count',
                    '/project' => 'Get URLs for project management with total count',
                    '/document' => 'Get URLs for document management with total count',
                    '/important-url' => 'Get URLs for important URL management with total count',
                    '/phone-number' => 'Get URLs for phone number management with total count',
                    '/user' => 'Get URLs for user management with total count',
                    '/resources' => 'Get total counts for all resources in the system',
                    '/trello-board' => 'Get URLs for Trello board management with total count',
                ],
                'footer' => 'Just type any of these shortcuts in your message to use them quickly! 🚀',
            ],
        ];

        // Default to English-only output
        $selected = $helpContent['english'];

        $output = "{$selected['title']}\n\n";
        $output .= "{$selected['intro']}\n";

        $counter = 1;
        foreach ($selected['shortcuts'] as $shortcut => $description) {
            $output .= "{$counter}. **{$shortcut}** - {$description}\n";
            $counter++;
        }

        $output .= "\n{$selected['footer']}";

        return $output;
    }

    /**
     * Tool: Get incomplete tasks with count and/or detailed breakdown by status.
     * Shortcut: /mytask
     */
    public function getIncompleteTasks(bool $includeDetails = true, bool $includeCount = true): string
    {
        $statuses = [
            'issue_tracker' => 'Issues',
            'wishlist' => 'Wishlist',
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'toreview' => 'To Review',
        ];

        $userId = $this->user->id;

        $query = Task::where(function ($query) use ($userId) {
            $query->whereJsonContains('assigned_to', (int) $userId)
                ->orWhereJsonContains('assigned_to', (string) $userId);
        })
            ->whereIn('status', array_keys($statuses));

        $tasks = $query->orderBy('status')
            ->orderBy('title')
            ->get();

        $tasksByStatus = [];

        foreach ($statuses as $status => $label) {
            $tasksByStatus[$status] = $tasks->where('status', $status)->map(function ($task) {
                return [
                    'task_name' => $task->title,
                    'url' => TaskResource::getUrl('edit', ['record' => $task]),
                    'due_date' => $task->due_date ?
                        (is_string($task->due_date) ? $task->due_date : $task->due_date->format('Y-m-d')) :
                        null,
                ];
            })->values()->all();
        }

        $result = [
            'tasks_by_status' => $tasksByStatus,
        ];

        if ($includeCount) {
            $result['task_count'] = $tasks->count();
        }

        $statusCounts = [];
        foreach ($statuses as $status => $label) {
            $statusCounts[$status] = count($tasksByStatus[$status]);
        }

        if (! $includeDetails) {
            return json_encode($result);
        }

        if ($tasks->isEmpty()) {
            $result['message'] = 'No incomplete tasks found assigned to you.';

            return json_encode($result);
        }

        $output = "You've got {$tasks->count()} incomplete tasks grouped by their current status. Here's a quick peek:\n\n";

        $output .= "Status totals:\n";
        foreach ($statuses as $status => $label) {
            $output .= "- **{$label}:** {$statusCounts[$status]}\n";
        }

        $output .= "\n";

        foreach ($statuses as $status => $label) {
            $statusTasks = $tasksByStatus[$status];
            $count = $statusCounts[$status];

            if ($count > 0) {
                $output .= "**{$label} ({$count} tasks)**\n\n";

                // Show first 3 tasks
                $displayTasks = array_slice($statusTasks, 0, 3);
                $counter = 1;

                foreach ($displayTasks as $task) {
                    // Truncate task name to 30 characters
                    $truncatedName = strlen($task['task_name']) > 30
                        ? substr($task['task_name'], 0, 30).'...'
                        : $task['task_name'];

                    $output .= "{$counter}. [**{$truncatedName}**]({$task['url']})";

                    if ($task['due_date']) {
                        // Format date as d/m/y
                        $dueDate = date('j/n/y', strtotime($task['due_date']));
                        $output .= " - Due date: {$dueDate}";
                    } else {
                        $output .= ' - Due date: -';
                    }

                    $output .= "\n";
                    $counter++;
                }

                // Show "and X more" if there are additional tasks
                $remaining = $count - 3;
                if ($remaining > 0) {
                    $output .= "*and {$remaining} more*\n";
                }

                $output .= "\n";
            }
        }

        $output .= 'Want more details on any of these or ready to dive into the others? Just say the word! 🚀';

        return $output;
    }

    /**
     * Tool: Get URLs for client management (create new, list all) with total count.
     * Shortcut: /client
     */
    public function getClientUrls(): string
    {
        $counts = $this->calculateModelCounts(Client::class);

        $output = "**Client Management** 👥\n\n";
        $output .= "There are **{$counts['active']}** clients in the system right now";

        if ($counts['trashed'] > 0) {
            $output .= " ({$counts['trashed']} archived)";
        }

        $output .= ".\n\n";
        $output .= "Here are the direct links to manage clients:\n\n";

        $createUrl = \App\Filament\Resources\ClientResource::getUrl('create');
        $listUrl = \App\Filament\Resources\ClientResource::getUrl('index');

        $output .= "**Create New Client**\n";
        $output .= "📝 [{$createUrl}]({$createUrl})\n";
        $output .= "Add a new client with company details, contact information, and project associations.\n\n";

        $output .= "**List All Clients**\n";
        $output .= "📋 [{$listUrl}]({$listUrl})\n";
        $output .= "View, search, and manage all existing clients in your database.\n\n";

        $output .= "💡 **Pro Tips:**\n";
        $output .= "• Use the global search to quickly find clients by name or email\n";
        $output .= "• Filter clients by status or creation date\n";

        $output .= 'Need help with something else? Just ask! 🚀';

        return $output;
    }

    /**
     * Tool: Get URLs for project management (create new, list all) with total count.
     * Shortcut: /project
     */
    public function getProjectUrls(): string
    {
        $counts = $this->calculateModelCounts(Project::class);

        $output = "**Project Management** 📁\n\n";
        $output .= "There are **{$counts['active']}** projects in the system right now";

        if ($counts['trashed'] > 0) {
            $output .= " ({$counts['trashed']} archived)";
        }

        $output .= ".\n\n";
        $output .= "Here are the direct links to manage projects:\n\n";

        $createUrl = \App\Filament\Resources\ProjectResource::getUrl('create');
        $listUrl = \App\Filament\Resources\ProjectResource::getUrl('index');

        $output .= "**Create New Project**\n";
        $output .= "📝 [{$createUrl}]({$createUrl})\n";
        $output .= "Start a new project with client assignment, document attachments, and important URLs.\n\n";

        $output .= "**List All Projects**\n";
        $output .= "📋 [{$listUrl}]({$listUrl})\n";
        $output .= "View, search, and manage all existing projects with filtering options.\n\n";

        $output .= "💡 **Pro Tips:**\n";
        $output .= "• Projects can be linked to clients and contain multiple documents\n";
        $output .= "• Use status filters to track project progress\n";
        $output .= "• Attach important URLs for quick reference\n\n";

        $output .= 'Need help with something else? Just ask! 🚀';

        return $output;
    }

    /**
     * Tool: Get URLs for document management (create new, list all) with total count.
     * Shortcut: /document
     */
    public function getDocumentUrls(): string
    {
        $counts = $this->calculateModelCounts(
            Document::class,
            fn ($query) => $query->visibleToUser($this->user->id)
        );

        $output = "**Document Management** 📄\n\n";
        $output .= "There are **{$counts['active']}** documents available to you right now";

        if ($counts['trashed'] > 0) {
            $output .= " ({$counts['trashed']} archived)";
        }

        $output .= ".\n\n";
        $output .= "Here are the direct links to manage documents:\n\n";

        $createUrl = \App\Filament\Resources\DocumentResource::getUrl('create');
        $listUrl = \App\Filament\Resources\DocumentResource::getUrl('index');

        $output .= "**Create New Document**\n";
        $output .= "📝 [{$createUrl}]({$createUrl})\n";
        $output .= "Upload and organize new documents with project associations.\n\n";

        $output .= "**List All Documents**\n";
        $output .= "📋 [{$listUrl}]({$listUrl})\n";
        $output .= "View, search, and manage all uploaded documents.\n\n";

        $output .= "💡 **Pro Tips:**\n";
        $output .= "• Documents can be linked to specific projects\n";
        $output .= "• Use file type filters to find documents quickly\n";
        $output .= "• Preview documents directly in the admin panel\n\n";

        $output .= 'Need help with something else? Just ask! 🚀';

        return $output;
    }

    /**
     * Tool: Get URLs for important URL management (create new, list all) with total count.
     * Shortcut: /important-url
     */
    public function getImportantUrlUrls(): string
    {
        $counts = $this->calculateModelCounts(ImportantUrl::class);

        $output = "**Important URL Management** 🔗\n\n";
        $output .= "There are **{$counts['active']}** important URLs in the system right now";

        if ($counts['trashed'] > 0) {
            $output .= " ({$counts['trashed']} archived)";
        }

        $output .= ".\n\n";
        $output .= "Here are the direct links to manage important URLs:\n\n";

        $createUrl = route('filament.admin.resources.important-urls.create');
        $listUrl = route('filament.admin.resources.important-urls.index');

        $output .= "**Create New Important URL**\n";
        $output .= "📝 [{$createUrl}]({$createUrl})\n";
        $output .= "Add important URLs with descriptions for quick reference and organization.\n\n";

        $output .= "**List All Important URLs**\n";
        $output .= "📋 [{$listUrl}]({$listUrl})\n";
        $output .= "View, search, and manage all important URLs with categories.\n\n";

        $output .= "💡 **Pro Tips:**\n";
        $output .= "• Categorize URLs for better organization\n";
        $output .= "• URLs can be linked to clients and projects\n";

        $output .= 'Need help with something else? Just ask! 🚀';

        return $output;
    }

    /**
     * Tool: Get URLs for phone number management (create new, list all) with total count.
     * Shortcut: /phone-number
     */
    public function getPhoneNumberUrls(): string
    {
        $counts = $this->calculateModelCounts(PhoneNumber::class);

        $output = "**Phone Number Management** 📞\n\n";
        $output .= "There are **{$counts['active']}** phone numbers in the system right now";

        if ($counts['trashed'] > 0) {
            $output .= " ({$counts['trashed']} archived)";
        }

        $output .= ".\n\n";
        $output .= "Here are the direct links to manage phone numbers:\n\n";

        $createUrl = \App\Filament\Resources\PhoneNumberResource::getUrl('create');
        $listUrl = \App\Filament\Resources\PhoneNumberResource::getUrl('index');

        $output .= "**Create New Phone Number**\n";
        $output .= "📝 [{$createUrl}]({$createUrl})\n";
        $output .= "Add new phone numbers with country codes and descriptions.\n\n";

        $output .= "**List All Phone Numbers**\n";
        $output .= "📋 [{$listUrl}]({$listUrl})\n";
        $output .= "View, search, and manage all phone numbers in your database.\n\n";

        $output .= "💡 **Pro Tips:**\n";
        $output .= "• Phone numbers include automatic country code formatting\n";
        $output .= "• Use search to find numbers by country or description\n";

        $output .= 'Need help with something else? Just ask! 🚀';

        return $output;
    }

    /**
     * Tool: Get URLs for user management (create new, list all) with total count.
     * Shortcut: /user
     */
    public function getUserUrls(): string
    {
        $counts = $this->calculateModelCounts(User::class);

        $output = "**User Management** 👤\n\n";
        $output .= "There are **{$counts['active']}** users in the system right now";

        if ($counts['trashed'] > 0) {
            $output .= " ({$counts['trashed']} archived)";
        }

        $output .= ".\n\n";
        $output .= "Here are the direct links to manage users:\n\n";

        $createUrl = \App\Filament\Resources\UserResource::getUrl('create');
        $listUrl = \App\Filament\Resources\UserResource::getUrl('index');

        $output .= "**Create New User**\n";
        $output .= "📝 [{$createUrl}]({$createUrl})\n";
        $output .= "Add new users with roles, permissions, and access control.\n\n";

        $output .= "**List All Users**\n";
        $output .= "📋 [{$listUrl}]({$listUrl})\n";
        $output .= "View, search, and manage all system users.\n\n";

        $output .= 'Need help with something else? Just ask! 🚀';

        return $output;
    }

    /**
     * Tool: Get URLs for Trello board management (create new, list all) with total count.
     * Shortcut: /trello-board
     */
    public function getTrelloBoardUrls(): string
    {
        $counts = $this->calculateModelCounts(TrelloBoard::class);

        $output = "**Trello Board Management** 📊\n\n";
        $output .= "There are **{$counts['active']}** Trello boards in the system right now";

        if ($counts['trashed'] > 0) {
            $output .= " ({$counts['trashed']} archived)";
        }

        $output .= ".\n\n";
        $output .= "Here are the direct links to manage Trello boards:\n\n";

        $createUrl = \App\Filament\Resources\TrelloBoardResource::getUrl('create');
        $listUrl = \App\Filament\Resources\TrelloBoardResource::getUrl('index');

        $output .= "**Create New Trello Board**\n";
        $output .= "📝 [{$createUrl}]({$createUrl})\n";
        $output .= "Create a new Trello board for organizing tasks and projects.\n\n";

        $output .= "**List All Trello Boards**\n";
        $output .= "📋 [{$listUrl}]({$listUrl})\n";
        $output .= "View, search, and manage all existing Trello boards.\n\n";

        $output .= "💡 **Pro Tips:**\n";
        $output .= "• Set the show_on_boards to true to show the Trello board in the navigation\n";
        $output .= "• Set the url to the Trello board URL\n";
        $output .= "• Set the name to the Trello board name\n\n";

        $output .= 'Need help with something else? Just ask! 🚀';

        return $output;
    }

    /**
     * Tool: Get total counts for all resources in the system.
     * Shortcut: /resources
     */
    public function getResourceCounts(): string
    {
        $counts = [
            'users' => $this->calculateModelCounts(User::class),
            'clients' => $this->calculateModelCounts(Client::class),
            'projects' => $this->calculateModelCounts(Project::class),
            'documents' => $this->calculateModelCounts(
                Document::class,
                fn ($query) => $query->visibleToUser($this->user->id)
            ),
            'important_urls' => $this->calculateModelCounts(ImportantUrl::class),
            'phone_numbers' => $this->calculateModelCounts(PhoneNumber::class),
            'trello_boards' => $this->calculateModelCounts(TrelloBoard::class),
        ];

        $output = "**Resource Counts Overview** 📊\n\n";
        $output .= "Here's the current count of all resources in your system:\n\n";

        $output .= "**👤 Users:** {$counts['users']['active']}";
        if ($counts['users']['trashed'] > 0) {
            $output .= " ({$counts['users']['trashed']} archived)";
        }
        $output .= "\n";

        $output .= "**👥 Clients:** {$counts['clients']['active']}";
        if ($counts['clients']['trashed'] > 0) {
            $output .= " ({$counts['clients']['trashed']} archived)";
        }
        $output .= "\n";

        $output .= "**📁 Projects:** {$counts['projects']['active']}";
        if ($counts['projects']['trashed'] > 0) {
            $output .= " ({$counts['projects']['trashed']} archived)";
        }
        $output .= "\n";

        $output .= "**📄 Documents:** {$counts['documents']['active']}";
        if ($counts['documents']['trashed'] > 0) {
            $output .= " ({$counts['documents']['trashed']} archived)";
        }
        $output .= "\n";

        $output .= "**🔗 Important URLs:** {$counts['important_urls']['active']}";
        if ($counts['important_urls']['trashed'] > 0) {
            $output .= " ({$counts['important_urls']['trashed']} archived)";
        }
        $output .= "\n";

        $output .= "**📞 Phone Numbers:** {$counts['phone_numbers']['active']}";
        if ($counts['phone_numbers']['trashed'] > 0) {
            $output .= " ({$counts['phone_numbers']['trashed']} archived)";
        }
        $output .= "\n";

        $output .= "**📊 Trello Boards:** {$counts['trello_boards']['active']}";
        if ($counts['trello_boards']['trashed'] > 0) {
            $output .= " ({$counts['trello_boards']['trashed']} archived)";
        }
        $output .= "\n\n";

        $output .= 'Want to see details for a specific resource? Use the individual shortcuts like /users or /clients! 🚀';

        return $output;
    }
}
