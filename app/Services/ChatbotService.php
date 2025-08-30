<?php

namespace App\Services;

use App\Filament\Resources\TaskResource;
use App\Models\Client;
use App\Models\Document;
use App\Models\ImportantUrl;
use App\Models\PhoneNumber;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

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
                    'description' => 'Get incomplete tasks assigned to the current user. Can return just the count, detailed breakdown by status, or both. Shortcut: /mytask',
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
                    'description' => 'Show all available shortcuts and commands. Shortcut: /help',
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
                    'description' => 'Get URLs for client management including create new client and list all clients with total count. Shortcut: /client',
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
                    'description' => 'Get URLs for project management including create new project and list all projects with total count. Shortcut: /project',
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
                    'description' => 'Get total counts for all resources in the system. Shortcut: /resources',
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
        $output = "**Available Shortcuts and Commands** 🤖\n\n";
        $output .= "Here are the shortcuts you can use to quickly access features:\n\n";

        $shortcuts = [
            '/help' => 'Show this help message with all available shortcuts',
            '/mytask' => 'Get your incomplete tasks with detailed breakdown by status',
            '/client' => 'Get URLs for client management with total count',
            '/project' => 'Get URLs for project management with total count',
            '/document' => 'Get URLs for document management with total count',
            '/important-url' => 'Get URLs for important URL management with total count',
            '/phone-number' => 'Get URLs for phone number management with total count',
            '/user' => 'Get URLs for user management with total count',
            '/resources' => 'Get total counts for all resources in the system',
        ];

        $counter = 1;
        foreach ($shortcuts as $shortcut => $description) {
            $output .= "{$counter}. **{$shortcut}**\n";
            $output .= "   {$description}\n\n";
            $counter++;
        }

        $output .= 'Just type any of these shortcuts in your message to use them quickly! 🚀';

        return $output;
    }

    /**
     * Tool: Get incomplete tasks with count and/or detailed breakdown by status.
     * Shortcut: /mytask
     */
    public function getIncompleteTasks(bool $includeDetails = true, bool $includeCount = true): string
    {
        $query = Task::where('assigned_to', $this->user->id)
            ->whereIn('status', ['todo', 'in_progress', 'toreview']);

        // If only count is needed, return early
        if (! $includeDetails && $includeCount) {
            $count = $query->count();

            return json_encode(['task_count' => $count]);
        }

        // Get the tasks for detailed breakdown
        $tasks = $query->orderBy('status')
            ->orderBy('title')
            ->get();

        $result = [];

        if ($includeCount) {
            $result['task_count'] = $tasks->count();
        }

        if (! $includeDetails) {
            return json_encode($result);
        }

        if ($tasks->isEmpty()) {
            $result['message'] = 'No incomplete tasks found assigned to you.';

            return json_encode($result);
        }

        // Group tasks by status
        $tasksByStatus = [
            'todo' => [],
            'in_progress' => [],
            'toreview' => [],
        ];

        foreach ($tasks as $task) {
            $tasksByStatus[$task->status][] = [
                'task_name' => $task->title,
                'url' => TaskResource::getUrl('edit', ['record' => $task]),
                'due_date' => $task->due_date ?
                    (is_string($task->due_date) ? $task->due_date : $task->due_date->format('Y-m-d')) :
                    null,
            ];
        }

        $result['tasks_by_status'] = $tasksByStatus;

        // If count only, return JSON
        if (! $includeDetails) {
            return json_encode($result);
        }

        // Format as structured text with proper styling
        $output = "You've got ".$tasks->count()." incomplete tasks grouped by their current status. Here's a quick peek:\n\n";

        // Define status labels and their counts
        $statusLabels = [
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'toreview' => 'To Review',
        ];

        foreach ($statusLabels as $status => $label) {
            $statusTasks = $tasksByStatus[$status];
            $count = count($statusTasks);

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
        $count = Client::whereNull('deleted_at')->count();

        $output = "**Client Management** 👥\n\n";
        $output .= "There are **{$count}** clients in the system right now.\n\n";
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
        $count = Project::whereNull('deleted_at')->count();

        $output = "**Project Management** 📁\n\n";
        $output .= "There are **{$count}** projects in the system right now.\n\n";
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
        $count = Document::whereNull('deleted_at')->count();

        $output = "**Document Management** 📄\n\n";
        $output .= "There are **{$count}** documents in the system right now.\n\n";
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
        $count = ImportantUrl::whereNull('deleted_at')->count();

        $output = "**Important URL Management** 🔗\n\n";
        $output .= "There are **{$count}** important URLs in the system right now.\n\n";
        $output .= "Here are the direct links to manage important URLs:\n\n";

        $createUrl = \App\Filament\Resources\ImportantUrlResource::getUrl('create');
        $listUrl = \App\Filament\Resources\ImportantUrlResource::getUrl('index');

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
        $count = PhoneNumber::whereNull('deleted_at')->count();

        $output = "**Phone Number Management** 📞\n\n";
        $output .= "There are **{$count}** phone numbers in the system right now.\n\n";
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
        $count = User::whereNull('deleted_at')->count();

        $output = "**User Management** 👤\n\n";
        $output .= "There are **{$count}** users in the system right now.\n\n";
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
     * Tool: Get total counts for all resources in the system.
     * Shortcut: /resources
     */
    public function getResourceCounts(): string
    {
        $counts = [
            'users' => User::whereNull('deleted_at')->count(),
            'clients' => Client::whereNull('deleted_at')->count(),
            'projects' => Project::whereNull('deleted_at')->count(),
            'documents' => Document::whereNull('deleted_at')->count(),
            'important_urls' => ImportantUrl::whereNull('deleted_at')->count(),
            'phone_numbers' => PhoneNumber::whereNull('deleted_at')->count(),
        ];

        $output = "**Resource Counts Overview** 📊\n\n";
        $output .= "Here's the current count of all resources in your system:\n\n";

        $output .= "**👤 Users:** {$counts['users']}\n";
        $output .= "**👥 Clients:** {$counts['clients']}\n";
        $output .= "**📁 Projects:** {$counts['projects']}\n";
        $output .= "**📄 Documents:** {$counts['documents']}\n";
        $output .= "**🔗 Important URLs:** {$counts['important_urls']}\n";
        $output .= "**📞 Phone Numbers:** {$counts['phone_numbers']}\n\n";

        $output .= 'Want to see details for a specific resource? Use the individual shortcuts like /users or /clients! 🚀';

        return $output;
    }
}
