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
}
