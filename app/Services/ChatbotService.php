<?php

namespace App\Services;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;

class ChatbotService
{
    /**
     * @var array<string, callable>
     */
    protected array $tools = [];

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->registerTools();
    }

    protected function registerTools(): void
    {
        $this->tools = [
            'get_incomplete_task_count' => [$this, 'getIncompleteTaskCount'],
            'get_task_url_by_name' => [$this, 'getTaskUrlByName'],
            'get_incomplete_tasks_by_status' => [$this, 'getIncompleteTasksByStatus'],
        ];
    }

    public function getToolDefinition(string $name): ?array
    {
        $definitions = [
            'get_incomplete_task_count' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_incomplete_task_count',
                    'description' => 'Get the number of incomplete tasks (Todo, In Progress, To Review) assigned to the current user.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass,
                        'required' => [],
                    ],
                ],
            ],
            'get_task_url_by_name' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_task_url_by_name',
                    'description' => 'Find tasks by name for the current user and get a list of URLs to edit them. Can return multiple results.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'task_name' => [
                                'type' => 'string',
                                'description' => 'The name or title of the task to search for.',
                            ],
                        ],
                        'required' => ['task_name'],
                    ],
                ],
            ],
            'get_incomplete_tasks_by_status' => [
                'type' => 'function',
                'function' => [
                    'name' => 'get_incomplete_tasks_by_status',
                    'description' => 'Get all incomplete tasks assigned to the current user, organized by status (Todo, In Progress, To Review) with URLs to edit them.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => new \stdClass,
                        'required' => [],
                    ],
                ],
            ],
        ];

        return $definitions[$name] ?? null;
    }

    public function getAllToolDefinitions(): array
    {
        return array_map([$this, 'getToolDefinition'], array_keys($this->tools));
    }

    public function executeTool(string $toolName, array $arguments): ?string
    {
        if (isset($this->tools[$toolName])) {
            return call_user_func($this->tools[$toolName], ...array_values($arguments));
        }

        return null;
    }

    /**
     * Tool: Get the count of incomplete tasks for the current user.
     */
    public function getIncompleteTaskCount(): string
    {
        $count = Task::where('assigned_to', $this->user->id)
            ->whereIn('status', ['todo', 'in_progress', 'toreview'])
            ->count();

        return json_encode(['task_count' => $count]);
    }

    /**
     * Tool: Get the URL for a task by its name.
     */
    public function getTaskUrlByName(string $taskName): string
    {
        // Limit search to tasks assigned to the current user for privacy and relevance
        $tasks = Task::where('title', 'like', "%{$taskName}%")
            ->where('assigned_to', $this->user->id)
            ->limit(5) // Limit to 5 results to avoid overwhelming the user
            ->get();

        if ($tasks->isEmpty()) {
            return json_encode(['error' => 'No tasks found with that name assigned to you.']);
        }

        $results = $tasks->map(function ($task) {
            return [
                'task_name' => $task->title,
                'url' => TaskResource::getUrl('edit', ['record' => $task]),
            ];
        });

        return json_encode(['tasks' => $results->toArray()]);
    }

    /**
     * Tool: Get all incomplete tasks organized by status with URLs.
     */
    public function getIncompleteTasksByStatus(): string
    {
        // Get all incomplete tasks for the current user
        $tasks = Task::where('assigned_to', $this->user->id)
            ->whereIn('status', ['todo', 'in_progress', 'toreview'])
            ->orderBy('status')
            ->orderBy('title')
            ->get();

        if ($tasks->isEmpty()) {
            return json_encode(['message' => 'No incomplete tasks found assigned to you.']);
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

        // Format as structured text with proper styling
        $output = "You've got " . $tasks->count() . " incomplete tasks grouped by their current status. Here's a quick peek:\n\n";

        // Define status labels and their counts
        $statusLabels = [
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'toreview' => 'To Review'
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
                        ? substr($task['task_name'], 0, 30) . '...'
                        : $task['task_name'];

                    $output .= "{$counter}. **{$truncatedName}** - [Task Details]({$task['url']})";

                    if ($task['due_date']) {
                        // Format date as d/m/y
                        $dueDate = date('j/n/y', strtotime($task['due_date']));
                        $output .= " - Due: {$dueDate}";
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

        $output .= "Want more details on any of these or ready to dive into the others? Just say the word! 🚀";

        return $output;
    }
}
