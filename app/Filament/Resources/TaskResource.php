<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;

class TaskResource extends Resource /*public static function shouldRegisterNavigation(): bool
{
return false;
}*/
{
    /**
     * Redirect global search result to Action Board and open the selected Task.
     */
    public static function getGlobalSearchResultUrl($record): string
    {
        // Redirect to Task edit page
        return static::getUrl('edit', ['record' => $record->getKey()]);
    }

    protected static ?string $model = Task::class;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = null; // Hide from navigation

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        $assignedUsers = $record->assignedToUsers();
        $assignedUserNames = $assignedUsers->map(function ($user) {
            return $user->short_name ?? $user->username ?? $user->name ?? 'User #'.$user->id;
        })->join(', ');

        $statusLabels = [
            'issue_tracker' => __('action.status.issue_tracker'),
            'todo' => __('task.status.todo'),
            'in_progress' => __('task.status.in_progress'),
            'toreview' => __('task.status.toreview'),
            'completed' => __('task.status.completed'),
            'archived' => __('task.status.archived'),
        ];

        return [
            __('task.search.status') => $statusLabels[$record->status] ?? $record->status,
            __('task.search.due_date') => $record->due_date ? \Carbon\Carbon::parse($record->due_date)->format('j/n/y') : null,
            __('task.search.assigned_to') => $assignedUserNames ?: __('task.search.no_assignment'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make([
                'default' => 1,
                'sm' => 1,
                'md' => 1,
                'lg' => 1,
                'xl' => 1,
                '2xl' => 5,
            ])
                ->schema([

                    // Main content (left side) - spans 2 columns
                    Forms\Components\Grid::make(1)
                        ->schema([

                            // Task Tabs
                            Forms\Components\Tabs::make('taskTabs')
                                ->tabs([

                                    // -----------------------------
                                    // Task Information
                                    // -----------------------------
                                    Forms\Components\Tabs\Tab::make(__('task.form.task_information'))
                                        ->schema([

                                            Forms\Components\Hidden::make('id')
                                                ->disabled()
                                                ->visible(false),

                                            // Enable/Disable Attachments, Resources, Additional Information
                                            Forms\Components\Fieldset::make('')
                                                ->label(function (?Task $record) {
                                                    return ! empty($record?->tracking_token) ? 'Disabled for Issue Tracker' : '';
                                                })
                                                ->schema([

                                                    Forms\Components\Grid::make(3)
                                                        ->schema([

                                                            // Attachments Toggle
                                                            Forms\Components\Toggle::make('enable_attachments')
                                                                ->label(__('task.form.enable_attachments'))
                                                                ->default(function (?Task $record) {
                                                                    // Enable if record has attachments (already cast to array)
                                                                    if ($record && $record->attachments) {
                                                                        $attachments = is_array($record->attachments) ? $record->attachments : [];

                                                                        return ! empty($attachments);
                                                                    }

                                                                    return false;
                                                                })
                                                                ->live()
                                                                ->dehydrated(false)
                                                                ->afterStateHydrated(function (Forms\Set $set, $state, ?Task $record) {
                                                                    // Double-check attachments on hydration and enable toggle if needed
                                                                    if ($record && $record->attachments) {
                                                                        $attachments = is_array($record->attachments) ? $record->attachments : [];
                                                                        if (! empty($attachments)) {
                                                                            $set('enable_attachments', true);
                                                                        }
                                                                    }
                                                                })
                                                                ->disabled(function (?Task $record) {
                                                                    return ! empty($record?->tracking_token);
                                                                })
                                                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                                    // When toggle is disabled, clear all attachments
                                                                    if (! $state) {
                                                                        $set('attachments', []);
                                                                    }
                                                                }),

                                                            // Task Resources Tab Toggle
                                                            Forms\Components\Toggle::make('enable_task_resources')
                                                                ->label(__('task.form.enable_task_resources'))
                                                                ->default(function (?Task $record) {
                                                                    // Enable if record has any resources
                                                                    if ($record) {
                                                                        $hasClient = ! empty($record->client);
                                                                        $hasProject = ! empty($record->project) && is_array($record->project);
                                                                        $hasDocument = ! empty($record->document) && is_array($record->document);
                                                                        $hasImportantUrl = ! empty($record->important_url) && is_array($record->important_url);

                                                                        return $hasClient || $hasProject || $hasDocument || $hasImportantUrl;
                                                                    }

                                                                    return false;
                                                                })
                                                                ->live()
                                                                ->dehydrated(false)
                                                                ->afterStateHydrated(function (Forms\Set $set, $state, ?Task $record) {
                                                                    // Double-check resources on hydration and enable toggle if needed
                                                                    if ($record) {
                                                                        $hasClient = ! empty($record->client);
                                                                        $hasProject = ! empty($record->project) && is_array($record->project);
                                                                        $hasDocument = ! empty($record->document) && is_array($record->document);
                                                                        $hasImportantUrl = ! empty($record->important_url) && is_array($record->important_url);

                                                                        if ($hasClient || $hasProject || $hasDocument || $hasImportantUrl) {
                                                                            $set('enable_task_resources', true);
                                                                        }
                                                                    }
                                                                })
                                                                ->disabled(function (?Task $record) {
                                                                    return ! empty($record?->tracking_token);
                                                                })
                                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                                    // When toggle is disabled, clear all resources
                                                                    if (! $state) {
                                                                        $set('client', null);
                                                                        $set('project', []);
                                                                        $set('document', []);
                                                                        $set('important_url', []);
                                                                    }
                                                                }),
                                                            // Additional Information Toggle
                                                            Forms\Components\Toggle::make('enable_additional_information')
                                                                ->label(__('task.form.enable_additional_information'))
                                                                ->default(function (?Task $record) {
                                                                    // Enable if record has extra_information
                                                                    if ($record && $record->extra_information) {
                                                                        $extraInfo = is_array($record->extra_information) ? $record->extra_information : [];

                                                                        return ! empty($extraInfo);
                                                                    }

                                                                    return false;
                                                                })
                                                                ->live()
                                                                ->dehydrated(false)
                                                                ->afterStateHydrated(function (Forms\Set $set, $state, ?Task $record) {
                                                                    // Double-check extra_information on hydration and enable toggle if needed
                                                                    if ($record && $record->extra_information) {
                                                                        $extraInfo = is_array($record->extra_information) ? $record->extra_information : [];
                                                                        if (! empty($extraInfo)) {
                                                                            $set('enable_additional_information', true);
                                                                        }
                                                                    }

                                                                    // For issue tracker tasks, always enable the toggle to preserve reporter data
                                                                    if ($record && $record->tracking_token) {
                                                                        $set('enable_additional_information', true);
                                                                    }
                                                                })
                                                                ->disabled(function (?Task $record) {
                                                                    return ! empty($record?->tracking_token);
                                                                })
                                                                ->afterStateUpdated(function ($state, Forms\Set $set, ?Task $record) {
                                                                    // When toggle is disabled, clear all extra_information (except for issue tracker tasks)
                                                                    if (! $state && (! $record || ! $record->tracking_token)) {
                                                                        $set('extra_information', []);
                                                                    }
                                                                }),

                                                        ]),

                                                ])
                                                ->columnSpanFull(),

                                            Forms\Components\Select::make('assigned_to')
                                                ->label(__('task.form.assign_to'))
                                                ->options(function () {
                                                    return \App\Models\User::withTrashed()
                                                        ->orderBy('username')
                                                        ->get()
                                                        ->mapWithKeys(fn ($u) => [
                                                            $u->id => ($u->username ?: 'User #'.$u->id).($u->deleted_at ? ' (deleted)' : ''),
                                                        ])
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->nullable()
                                                ->multiple()
                                                ->formatStateUsing(fn ($state, ?Task $record) => $record?->assigned_to)
                                                ->default(fn (?Task $record) => $record?->assigned_to)
                                                ->dehydrated(),

                                            Forms\Components\TextInput::make('title')
                                                ->label(__('task.form.title'))
                                                ->required()
                                                ->placeholder(__('task.form.title_placeholder'))
                                                ->columnSpanFull(),

                                            Forms\Components\Grid::make(3)
                                                ->schema([

                                                    Forms\Components\DatePicker::make('due_date')
                                                        ->label(__('task.form.due_date'))
                                                        ->placeholder('dd/mm/yyyy')
                                                        ->native(false)
                                                        ->displayFormat('j/n/y'),

                                                    Forms\Components\Select::make('status')
                                                        ->label(__('task.form.status'))
                                                        ->options([
                                                            'issue_tracker' => __('action.status.issue_tracker'),
                                                            'todo' => __('task.status.todo'),
                                                            'in_progress' => __('task.status.in_progress'),
                                                            'toreview' => __('task.status.toreview'),
                                                            'completed' => __('task.status.completed'),
                                                            'archived' => __('task.status.archived'),
                                                        ])
                                                        ->searchable(),

                                                    Forms\Components\Select::make('priority')
                                                        ->label(__('task.form.priority'))
                                                        ->options([
                                                            'low' => __('task.priority.low'),
                                                            'medium' => __('task.priority.medium'),
                                                            'high' => __('task.priority.high'),
                                                        ])
                                                        ->default('medium')
                                                        ->searchable(),

                                                ]),

                                            // Issue Tracker Code Display
                                            // Show if task was created from issue tracker submission (has tracking_token)
                                            // This persists even when task is moved to other columns
                                            // Displays both: Issue Tracker Code (project) and Tracking Token (task)
                                            Forms\Components\TextInput::make('issue_tracker_code_display')
                                                ->label(__('task.form.issue_tracker_code'))
                                                ->default(function (?Task $record) {
                                                    if (! $record || ! $record->tracking_token) {
                                                        return null;
                                                    }

                                                    $parts = [];

                                                    // Get project issue tracker code
                                                    $projectIds = $record->project ?? [];
                                                    if (! empty($projectIds) && is_array($projectIds)) {
                                                        $projectId = $projectIds[0] ?? null;
                                                        if ($projectId) {
                                                            $project = \App\Models\Project::find($projectId);
                                                            if ($project && $project->issue_tracker_code) {
                                                                $parts[] = $project->issue_tracker_code;
                                                            }
                                                        }
                                                    }

                                                    // Get task tracking token
                                                    if ($record->tracking_token) {
                                                        $parts[] = $record->tracking_token;
                                                    }

                                                    return ! empty($parts) ? implode(' / ', $parts) : null;
                                                })
                                                ->afterStateHydrated(function (Forms\Set $set, $state, ?Task $record) {
                                                    // Ensure value is set even if default didn't work
                                                    if (empty($state) && $record && $record->tracking_token) {
                                                        $parts = [];

                                                        // Get project issue tracker code
                                                        $projectIds = $record->project ?? [];
                                                        if (! empty($projectIds) && is_array($projectIds)) {
                                                            $projectId = $projectIds[0] ?? null;
                                                            if ($projectId) {
                                                                $project = \App\Models\Project::find($projectId);
                                                                if ($project && $project->issue_tracker_code) {
                                                                    $parts[] = $project->issue_tracker_code;
                                                                }
                                                            }
                                                        }

                                                        // Get task tracking token
                                                        if ($record->tracking_token) {
                                                            $parts[] = $record->tracking_token;
                                                        }

                                                        if (! empty($parts)) {
                                                            $set('issue_tracker_code_display', implode(' / ', $parts));
                                                        }
                                                    }
                                                })
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->extraAttributes([
                                                    'class' => 'font-mono',
                                                ])
                                                ->prefixAction(
                                                    Forms\Components\Actions\Action::make('openIssueTracker')
                                                        ->icon('heroicon-o-arrow-top-right-on-square')
                                                        ->label(__('task.form.open_issue_tracker'))
                                                        ->url(function (?Task $record) {
                                                            if (! $record) {
                                                                return null;
                                                            }

                                                            $projectIds = $record->project ?? [];
                                                            if (empty($projectIds) || ! is_array($projectIds)) {
                                                                return null;
                                                            }

                                                            $projectId = $projectIds[0] ?? null;
                                                            if (! $projectId) {
                                                                return null;
                                                            }

                                                            $project = \App\Models\Project::find($projectId);

                                                            return $project && $project->issue_tracker_code
                                                                ? route('issue-tracker.show', ['project' => $project->issue_tracker_code])
                                                                : null;
                                                        })
                                                        ->openUrlInNewTab()
                                                        ->visible(function (?Task $record) {
                                                            if (! $record) {
                                                                return false;
                                                            }

                                                            $projectIds = $record->project ?? [];
                                                            if (empty($projectIds) || ! is_array($projectIds)) {
                                                                return false;
                                                            }

                                                            $projectId = $projectIds[0] ?? null;
                                                            if (! $projectId) {
                                                                return false;
                                                            }

                                                            $project = \App\Models\Project::find($projectId);

                                                            return $project && $project->issue_tracker_code;
                                                        })
                                                )
                                                ->suffixAction(
                                                    Forms\Components\Actions\Action::make('viewStatus')
                                                        ->icon('heroicon-o-eye')
                                                        ->label(__('task.form.view_issue_status'))
                                                        ->url(function (?Task $record) {
                                                            if (! $record || ! $record->tracking_token) {
                                                                return null;
                                                            }

                                                            return route('issue-tracker.status', ['token' => $record->tracking_token]);
                                                        })
                                                        ->openUrlInNewTab()
                                                        ->visible(fn (?Task $record) => $record && $record->tracking_token)
                                                )
                                                ->visible(function (?Task $record) {
                                                    // Show if task has tracking_token (created from issue tracker)
                                                    // This way it persists even when task is moved to other columns
                                                    if (! $record || ! $record->tracking_token) {
                                                        return false;
                                                    }

                                                    return true; // Show if tracking_token exists, even without project
                                                })
                                                ->columnSpanFull(),

                                            Forms\Components\RichEditor::make('description')
                                                ->label(__('task.form.description'))
                                                ->toolbarButtons([
                                                    'bold',
                                                    'italic',
                                                    'strike',
                                                    'bulletList',
                                                    'orderedList',
                                                    'link',
                                                    'codeBlock',
                                                ])
                                                ->extraAttributes(['style' => 'resize: vertical;'])
                                                ->reactive()
                                                ->afterStateHydrated(function (Forms\Set $set, $state, ?Task $record) {
                                                    // Convert plain text with \n to HTML if description is plain text
                                                    if ($state && ! preg_match('/<[^>]+>/', $state)) {
                                                        // It's plain text, convert \n to <br> and wrap in <p>
                                                        $html = nl2br(e($state));
                                                        $set('description', $html);
                                                    }
                                                })
                                                ->dehydrateStateUsing(function ($state) {
                                                    // Ensure HTML is properly formatted when saving
                                                    if (empty($state)) {
                                                        return $state;
                                                    }

                                                    // If it's plain text (no HTML tags), convert to HTML
                                                    if (! preg_match('/<[^>]+>/', $state)) {
                                                        return nl2br(e($state));
                                                    }

                                                    return $state;
                                                })
                                                ->helperText(function (Forms\Get $get) {
                                                    $raw = $get('description') ?? '';
                                                    $noHtml = strip_tags($raw);
                                                    $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $remaining = 500 - mb_strlen($decoded);

                                                    return __('task.edit.description_helper', ['count' => $remaining]);
                                                })
                                                ->rule(function (Forms\Get $get): \Closure {
                                                    return function (string $attribute, $value, \Closure $fail) {
                                                        $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                        if (mb_strlen($textOnly) > 500) {
                                                            $fail(__('task.edit.description_warning'));
                                                        }
                                                    };
                                                })
                                                ->nullable()
                                                ->columnSpanFull(),

                                            Forms\Components\FileUpload::make('attachments')
                                                ->label(function (Forms\Get $get) {
                                                    $attachments = $get('attachments') ?? [];
                                                    $count = count($attachments);
                                                    $label = __('task.form.attachments');

                                                    if ($count > 0) {
                                                        return new \Illuminate\Support\HtmlString(
                                                            $label.' <span class="ml-1 inline-flex items-center rounded-full bg-primary-500/15 px-3 py-0.5 text-xs font-bold text-primary-600 dark:bg-primary-800/5 border border-primary-600 dark:border-primary-700 dark:text-primary-500">'.$count.'</span>'
                                                        );
                                                    }

                                                    return $label;
                                                })
                                                ->helperText(__('task.form.attachments_helper'))
                                                ->multiple()
                                                ->openable()
                                                ->panelLayout('grid')
                                                ->reorderable()
                                                ->appendFiles()
                                                ->itemPanelAspectRatio('0.25')
                                                ->image()
                                                ->acceptedFileTypes(['image/*', 'video/*', 'application/pdf'])
                                                ->maxSize(20480) // 20MB
                                                ->directory('tasks')
                                                ->preserveFilenames()
                                                ->moveFiles()
                                                ->live()
                                                ->nullable()
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    // Automatically enable toggle when files are uploaded
                                                    if (! empty($state) && is_array($state)) {
                                                        $set('enable_attachments', true);
                                                    } elseif (empty($state)) {
                                                        // Disable toggle when all files are removed
                                                        $set('enable_attachments', false);
                                                    }
                                                })
                                                ->afterStateHydrated(function (Forms\Set $set, $state, ?Task $record) {
                                                    // Enable toggle when form loads with existing attachments
                                                    if (! empty($state) && is_array($state)) {
                                                        $set('enable_attachments', true);
                                                    } elseif ($record && $record->attachments && is_array($record->attachments) && ! empty($record->attachments)) {
                                                        $set('enable_attachments', true);
                                                    }
                                                })
                                                ->visible(fn (Forms\Get $get) => (bool) $get('enable_attachments'))
                                                ->columnSpanFull(),
                                        ]),

                                    // -----------------------------
                                    // Task Resources
                                    // -----------------------------
                                    Forms\Components\Tabs\Tab::make(__('task.form.task_resources'))
                                        // Badge for the tab
                                        ->badge(function (Get $get) {
                                            // Count the number of resources selected
                                            $client = $get('client') ? 1 : 0;
                                            $project = $get('project') ?? [];
                                            $document = $get('document') ?? [];
                                            $importantUrl = $get('important_url') ?? [];

                                            // Ensure arrays are countable (handle corrupted data)
                                            $projectCount = is_array($project) ? count($project) : 0;
                                            $documentCount = is_array($document) ? count($document) : 0;
                                            $importantUrlCount = is_array($importantUrl) ? count($importantUrl) : 0;

                                            return $client + $projectCount + $documentCount + $importantUrlCount ?: null;
                                        })
                                        ->visible(fn (Forms\Get $get) => (bool) $get('enable_task_resources'))
                                        ->schema([

                                            // Client
                                            Forms\Components\Select::make('client')
                                                ->label(__('task.form.client'))
                                                ->helperText(function (?Task $record) {
                                                    if ($record && $record->tracking_token) {
                                                        return __('task.form.client_helper_issue_tracker');
                                                    }

                                                    return __('task.form.client_helper');
                                                })
                                                ->options(function () {
                                                    return \App\Models\Client::withTrashed()
                                                        ->orderBy('company_name')
                                                        ->get()
                                                        ->mapWithKeys(fn ($c) => [
                                                            $c->id => $c->pic_name.' ('.($c->company_name ?: 'Company #'.$c->id).')'.($c->deleted_at ? ' (deleted)' : ''),
                                                        ])
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->nullable()
                                                ->default(fn (?Task $record) => $record?->client)
                                                ->dehydrated()
                                                ->live()
                                                ->reactive()
                                                ->disabled(fn (?Task $record) => $record && $record->tracking_token)
                                                ->prefixAction(
                                                    // Open the client in a new tab
                                                    Forms\Components\Actions\Action::make('openClient')
                                                        ->icon('heroicon-o-pencil-square')
                                                        ->url(function (Forms\Get $get) {
                                                            $clientId = $get('client');
                                                            if (! $clientId) {
                                                                return null;
                                                            }

                                                            return \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $clientId]);
                                                        })
                                                        ->openUrlInNewTab()
                                                        ->visible(fn (Forms\Get $get) => (bool) $get('client'))
                                                )
                                                ->suffixAction(
                                                    Forms\Components\Actions\Action::make('createClient')
                                                        ->icon('heroicon-o-plus')
                                                        ->url(\App\Filament\Resources\ClientResource::getUrl('create'))
                                                        ->openUrlInNewTab()
                                                        ->label(__('task.form.create_client'))
                                                        ->visible(fn (?Task $record) => ! ($record && $record->tracking_token))
                                                )
                                                ->afterStateHydrated(function (Forms\Set $set, $state, ?Task $record) {
                                                    // Auto-populate client from project for issue tracker tasks
                                                    if ($record && $record->tracking_token) {
                                                        $projectIds = $record->project ?? [];
                                                        if (! empty($projectIds) && is_array($projectIds)) {
                                                            $projectId = $projectIds[0] ?? null;
                                                            if ($projectId) {
                                                                $project = \App\Models\Project::find($projectId);
                                                                if ($project && $project->client_id) {
                                                                    $set('client', $project->client_id);
                                                                    $set('enable_task_resources', true);
                                                                }
                                                            }
                                                        }
                                                    }
                                                })
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    // Skip auto-population for issue tracker tasks
                                                    if (request()->route('record')) {
                                                        $task = Task::find(request()->route('record'));
                                                        if ($task && $task->tracking_token) {
                                                            return;
                                                        }
                                                    }

                                                    // Automatically enable toggle when client is selected
                                                    if ($state) {
                                                        $set('enable_task_resources', true);
                                                    }

                                                    // If a client is selected, get all projects, documents, and important URLs for selected client
                                                    if ($state) {
                                                        // Get projects for selected client
                                                        $projects = \App\Models\Project::where('client_id', $state)
                                                            ->withTrashed()
                                                            ->orderBy('title')
                                                            ->get()
                                                            ->pluck('id')
                                                            ->toArray();

                                                        // Get documents for projects of selected client
                                                        $documents = \App\Models\Document::whereHas('project', function ($query) use ($state) {
                                                            $query->where('client_id', $state);
                                                        })
                                                            ->withTrashed()
                                                            ->orderBy('title')
                                                            ->get()
                                                            ->pluck('id')
                                                            ->toArray();

                                                        // Get important URLs for projects of selected client
                                                        $importantUrls = \App\Models\ImportantUrl::whereHas('project', function ($query) use ($state) {
                                                            $query->where('client_id', $state);
                                                        })
                                                            ->withTrashed()
                                                            ->orderBy('title')
                                                            ->get()
                                                            ->pluck('id')
                                                            ->toArray();

                                                        // Auto-select all projects, documents, and important URLs for the client
                                                        $set('project', $projects);
                                                        $set('document', $documents);
                                                        $set('important_url', $importantUrls);
                                                    } else {
                                                        // Clear selections when no client is selected
                                                        $set('project', []);
                                                        $set('document', []);
                                                        $set('important_url', []);
                                                    }
                                                }),

                                            // Projects
                                            Forms\Components\Grid::make(1)
                                                ->schema([
                                                    Forms\Components\Select::make('project')
                                                        ->label(__('task.form.project'))
                                                        ->helperText(function (?Task $record) {
                                                            if ($record && $record->tracking_token) {
                                                                return __('task.form.project_helper_issue_tracker');
                                                            }

                                                            return __('task.form.project_helper');
                                                        })
                                                        ->options(function (Forms\Get $get, $record) {
                                                            // For issue tracker tasks, show the project even without client
                                                            if ($record && $record->tracking_token) {
                                                                $projectIds = $record->project ?? [];
                                                                if (! empty($projectIds) && is_array($projectIds)) {
                                                                    return \App\Models\Project::whereIn('id', $projectIds)
                                                                        ->withTrashed()
                                                                        ->orderBy('title')
                                                                        ->get()
                                                                        ->mapWithKeys(fn ($p) => [
                                                                            $p->id => str($p->title)->limit(20).($p->deleted_at ? ' (deleted)' : ''),
                                                                        ])
                                                                        ->toArray();
                                                                }
                                                            }

                                                            // If no client is selected, return an empty array
                                                            $clientId = $get('client');
                                                            if (! $clientId) {
                                                                return [];
                                                            }

                                                            return \App\Models\Project::where('client_id', $clientId)
                                                                ->withTrashed()
                                                                ->orderBy('title')
                                                                ->get()
                                                                ->mapWithKeys(fn ($p) => [
                                                                    $p->id => str($p->title)->limit(20).($p->deleted_at ? ' (deleted)' : ''),
                                                                ])
                                                                ->toArray();
                                                        })
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->nullable()
                                                        ->multiple()
                                                        ->default(fn (?Task $record) => $record?->project)
                                                        ->dehydrated()
                                                        ->live()
                                                        ->reactive()
                                                        ->disabled(fn (?Task $record) => $record && $record->tracking_token)
                                                        ->suffixAction(
                                                            Forms\Components\Actions\Action::make('createProject')
                                                                ->icon('heroicon-o-plus')
                                                                ->url(\App\Filament\Resources\ProjectResource::getUrl('create'))
                                                                ->openUrlInNewTab()
                                                                ->label(__('task.form.create_project'))
                                                                ->visible(fn (?Task $record) => ! ($record && $record->tracking_token))
                                                        )
                                                        ->afterStateHydrated(function (Forms\Set $set, $state, ?Task $record) {
                                                            // Auto-populate client and resources from project for issue tracker tasks
                                                            if ($record && $record->tracking_token) {
                                                                $projectIds = $state ?? $record->project ?? [];
                                                                if (! empty($projectIds) && is_array($projectIds)) {
                                                                    $projectId = $projectIds[0] ?? null;
                                                                    if ($projectId) {
                                                                        $project = \App\Models\Project::find($projectId);
                                                                        if ($project) {
                                                                            // Set client from project
                                                                            if ($project->client_id) {
                                                                                $set('client', $project->client_id);
                                                                            }

                                                                            // Get all documents for the project
                                                                            $documents = \App\Models\Document::where('project_id', $projectId)
                                                                                ->withTrashed()
                                                                                ->orderBy('title')
                                                                                ->pluck('id')
                                                                                ->toArray();

                                                                            // Get all important URLs for the project
                                                                            $importantUrls = \App\Models\ImportantUrl::where('project_id', $projectId)
                                                                                ->withTrashed()
                                                                                ->orderBy('title')
                                                                                ->pluck('id')
                                                                                ->toArray();

                                                                            // Auto-populate documents and important URLs
                                                                            if (! empty($documents)) {
                                                                                $set('document', $documents);
                                                                            }
                                                                            if (! empty($importantUrls)) {
                                                                                $set('important_url', $importantUrls);
                                                                            }

                                                                            // Enable task resources toggle if any resources exist
                                                                            if ($project->client_id || ! empty($documents) || ! empty($importantUrls)) {
                                                                                $set('enable_task_resources', true);
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        })
                                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                            // Skip auto-population for issue tracker tasks
                                                            if (request()->route('record')) {
                                                                $task = Task::find(request()->route('record'));
                                                                if ($task && $task->tracking_token) {
                                                                    return;
                                                                }
                                                            }

                                                            // Automatically enable toggle when projects are selected
                                                            $selectedProjects = $state ?? [];
                                                            if (! empty($selectedProjects)) {
                                                                $set('enable_task_resources', true);
                                                            }

                                                            // If no projects are selected, clear all documents
                                                            $currentDocuments = $get('document') ?? [];

                                                            if (empty($selectedProjects)) {
                                                                // No projects selected, clear all documents
                                                                $set('document', []);

                                                                return;
                                                            }

                                                            // Get all documents for the selected projects
                                                            $availableDocuments = \App\Models\Document::whereIn('project_id', $selectedProjects)
                                                                ->withTrashed()
                                                                ->pluck('id')
                                                                ->toArray();

                                                            // Keep existing documents that are still valid + add new ones for newly selected projects
                                                            $validCurrentDocuments = array_intersect($currentDocuments, $availableDocuments);

                                                            // Auto-add documents for newly selected projects if they weren't already selected
                                                            $newDocuments = array_diff($availableDocuments, $currentDocuments);

                                                            // Merge the valid documents with the new documents
                                                            $finalDocuments = array_unique(array_merge($validCurrentDocuments, $newDocuments));

                                                            // Set the documents
                                                            $set('document', $finalDocuments);
                                                        }),

                                                    // Documents
                                                    Forms\Components\Select::make('document')
                                                        ->label(__('task.form.document'))
                                                        ->helperText(function (?Task $record) {
                                                            if ($record && $record->tracking_token) {
                                                                return __('task.form.document_helper_issue_tracker');
                                                            }

                                                            return __('task.form.document_helper');
                                                        })
                                                        ->options(function (Forms\Get $get, $record) {
                                                            $selectedProjects = $get('project') ?? [];

                                                            // For issue tracker tasks, use project from record if available
                                                            if ($record && $record->tracking_token && empty($selectedProjects)) {
                                                                $projectIds = $record->project ?? [];
                                                                if (! empty($projectIds) && is_array($projectIds)) {
                                                                    $selectedProjects = $projectIds;
                                                                }
                                                            }

                                                            if (empty($selectedProjects)) {
                                                                // If no projects are selected, get all documents for the client
                                                                $clientId = $get('client');
                                                                if (! $clientId) {
                                                                    return [];
                                                                }

                                                                // Get all documents for the client
                                                                return \App\Models\Document::whereHas('project', function ($query) use ($clientId) {
                                                                    $query->where('client_id', $clientId);
                                                                })
                                                                    ->withTrashed()
                                                                    ->orderBy('title')
                                                                    ->get()
                                                                    ->mapWithKeys(fn ($d) => [
                                                                        $d->id => str($d->title)->limit(20).($d->deleted_at ? ' (deleted)' : ''),
                                                                    ])
                                                                    ->toArray();
                                                            }

                                                            // Get all documents for the selected projects
                                                            return \App\Models\Document::whereIn('project_id', $selectedProjects)
                                                                ->withTrashed()
                                                                ->orderBy('title')
                                                                ->get()
                                                                ->mapWithKeys(fn ($d) => [
                                                                    $d->id => str($d->title)->limit(20).($d->deleted_at ? ' (deleted)' : ''),
                                                                ])
                                                                ->toArray();
                                                        })
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->nullable()
                                                        ->multiple()
                                                        ->default(fn (?Task $record) => $record?->document)
                                                        ->dehydrated()
                                                        ->live()
                                                        ->reactive()
                                                        ->suffixAction(
                                                            Forms\Components\Actions\Action::make('createDocument')
                                                                ->icon('heroicon-o-plus')
                                                                ->url(\App\Filament\Resources\DocumentResource::getUrl('create'))
                                                                ->openUrlInNewTab()
                                                                ->label(__('task.form.create_document'))
                                                                ->visible(fn (?Task $record) => ! ($record && $record->tracking_token))
                                                        )
                                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                            // Automatically enable toggle when documents are selected
                                                            $selectedDocuments = $state ?? [];
                                                            if (! empty($selectedDocuments) && is_array($selectedDocuments)) {
                                                                $set('enable_task_resources', true);
                                                            }
                                                        }),

                                                    // Important URLs
                                                    Forms\Components\Select::make('important_url')
                                                        ->label(__('task.form.important_url'))
                                                        ->helperText(function (?Task $record) {
                                                            if ($record && $record->tracking_token) {
                                                                return __('task.form.important_url_helper_issue_tracker');
                                                            }

                                                            return __('task.form.important_url_helper');
                                                        })
                                                        ->options(function (Forms\Get $get, $record) {
                                                            $selectedProjects = $get('project') ?? [];
                                                            $clientId = $get('client');

                                                            // For issue tracker tasks, use project from record if available
                                                            if ($record && $record->tracking_token && empty($selectedProjects)) {
                                                                $projectIds = $record->project ?? [];
                                                                if (! empty($projectIds) && is_array($projectIds)) {
                                                                    $selectedProjects = $projectIds;
                                                                }
                                                            }

                                                            // If projects are selected, get important URLs for those projects
                                                            if (! empty($selectedProjects)) {
                                                                return \App\Models\ImportantUrl::whereIn('project_id', $selectedProjects)
                                                                    ->withTrashed()
                                                                    ->orderBy('title')
                                                                    ->get()
                                                                    ->mapWithKeys(fn ($i) => [
                                                                        $i->id => str($i->title)->limit(20).($i->deleted_at ? ' (deleted)' : ''),
                                                                    ])
                                                                    ->toArray();
                                                            }

                                                            // If no client is selected, return an empty array
                                                            if (! $clientId) {
                                                                return [];
                                                            }

                                                            // Get all important URLs for the client
                                                            return \App\Models\ImportantUrl::where('client_id', $clientId)
                                                                ->withTrashed()
                                                                ->orderBy('title')
                                                                ->get()
                                                                ->mapWithKeys(fn ($i) => [
                                                                    $i->id => str($i->title)->limit(20).($i->deleted_at ? ' (deleted)' : ''),
                                                                ])
                                                                ->toArray();
                                                        })
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->nullable()
                                                        ->multiple()
                                                        ->default(fn (?Task $record) => $record?->important_url)
                                                        ->dehydrated()
                                                        ->live()
                                                        ->reactive()
                                                        ->suffixAction(
                                                            Forms\Components\Actions\Action::make('createImportantUrl')
                                                                ->icon('heroicon-o-plus')
                                                                ->url(\App\Filament\Resources\ImportantUrlResource::getUrl('create'))
                                                                ->openUrlInNewTab()
                                                                ->label(__('task.form.create_important_url'))
                                                                ->visible(fn (?Task $record) => ! ($record && $record->tracking_token))
                                                        )
                                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                            // Automatically enable toggle when important URLs are selected
                                                            $selectedUrls = $state ?? [];
                                                            if (! empty($selectedUrls) && is_array($selectedUrls)) {
                                                                $set('enable_task_resources', true);
                                                            }
                                                        }),

                                                    // Display selected items with clickable links
                                                    Forms\Components\ViewField::make('selected_items_links')
                                                        ->view('filament.components.selected-items-links')
                                                        ->viewData(function (Forms\Get $get) {
                                                            $clientId = $get('client');
                                                            $selectedProjects = $get('project') ?? [];
                                                            $selectedDocuments = $get('document') ?? [];
                                                            $selectedUrls = $get('important_url') ?? [];

                                                            return [
                                                                'clientId' => $clientId,
                                                                'selectedProjects' => $selectedProjects,
                                                                'selectedDocuments' => $selectedDocuments,
                                                                'selectedUrls' => $selectedUrls,
                                                            ];
                                                        })
                                                        ->visible(
                                                            fn (Forms\Get $get) => ! empty($get('project')) ||
                                                            ! empty($get('document')) ||
                                                            ! empty($get('important_url'))
                                                        )
                                                        ->live()
                                                        ->columnSpanFull(),
                                                ]),

                                        ]),

                                    // -----------------------------
                                    // Task Additional Information
                                    // -----------------------------
                                    Forms\Components\Tabs\Tab::make(__('task.form.additional_information'))
                                        ->badge(function (Get $get) {
                                            $extraInfo = $get('extra_information') ?? [];

                                            return count($extraInfo) ?: null;
                                        })
                                        ->visible(fn (Forms\Get $get) => (bool) $get('enable_additional_information'))
                                        ->schema([

                                            Forms\Components\Repeater::make('extra_information')
                                                ->label(__('task.form.extra_information'))
                                                ->schema([

                                                    Forms\Components\TextInput::make('title')
                                                        ->label(__('task.form.title'))
                                                        ->maxLength(100)
                                                        ->columnSpanFull()
                                                        ->disabled(function (Forms\Get $get) {
                                                            $title = (string) ($get('title') ?? '');
                                                            $lockedTitles = [
                                                                'Reporter Name',
                                                                'Communication Preference',
                                                                'Reporter Email',
                                                                'Reporter WhatsApp',
                                                                'Submitted on',
                                                            ];

                                                            return in_array($title, $lockedTitles, true);
                                                        }),

                                                    Forms\Components\RichEditor::make('value')
                                                        ->label(__('task.form.value'))
                                                        ->toolbarButtons([
                                                            'bold',
                                                            'italic',
                                                            'strike',
                                                            'bulletList',
                                                            'orderedList',
                                                            'link',
                                                            'codeBlock',
                                                        ])
                                                        ->extraAttributes(['style' => 'resize: vertical;'])
                                                        ->reactive()
                                                        ->disabled(function (Forms\Get $get) {
                                                            $title = (string) ($get('title') ?? '');
                                                            $lockedTitles = [
                                                                'Reporter Name',
                                                                'Communication Preference',
                                                                'Reporter Email',
                                                                'Reporter WhatsApp',
                                                                'Submitted on',
                                                            ];

                                                            return in_array($title, $lockedTitles, true);
                                                        })
                                                        ->helperText(function (Forms\Get $get) {
                                                            $raw = $get('value') ?? '';
                                                            $noHtml = strip_tags($raw);
                                                            $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                            $remaining = 500 - mb_strlen($decoded);

                                                            return __('task.edit.extra_information_helper', ['count' => $remaining]);
                                                        })
                                                        ->rule(function (Forms\Get $get): \Closure {
                                                            return function (string $attribute, $value, \Closure $fail) {
                                                                $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                                if (mb_strlen($textOnly) > 500) {
                                                                    $fail(__('task.edit.extra_information_warning'));
                                                                }
                                                            };
                                                        })
                                                        ->columnSpanFull(),
                                                ])
                                                ->defaultItems(1)
                                                ->addActionLabel(__('task.form.add_extra_info'))
                                                ->addActionAlignment(Alignment::Start)
                                                ->cloneable()
                                                ->reorderable()
                                                ->cloneAction(function (\Filament\Forms\Components\Actions\Action $action, \Filament\Forms\Components\Repeater $component) {
                                                    $lockedTitles = [
                                                        'Reporter Name',
                                                        'Communication Preference',
                                                        'Reporter Email',
                                                        'Reporter WhatsApp',
                                                        'Submitted on',
                                                    ];

                                                    return $action->visible(function (array $arguments) use ($component, $lockedTitles) {
                                                        $itemData = (array) $component->getRawItemState($arguments['item']);
                                                        $title = (string) ($itemData['title'] ?? '');

                                                        return ! in_array($title, $lockedTitles, true);
                                                    });
                                                })
                                                ->deleteAction(function (\Filament\Forms\Components\Actions\Action $action, \Filament\Forms\Components\Repeater $component) {
                                                    $lockedTitles = [
                                                        'Reporter Name',
                                                        'Communication Preference',
                                                        'Reporter Email',
                                                        'Reporter WhatsApp',
                                                        'Submitted on',
                                                    ];

                                                    return $action->visible(function (array $arguments) use ($component, $lockedTitles) {
                                                        $itemData = (array) $component->getRawItemState($arguments['item']);
                                                        $title = (string) ($itemData['title'] ?? '');

                                                        return ! in_array($title, $lockedTitles, true);
                                                    });
                                                })
                                                ->collapsible(true)
                                                ->collapsed()
                                                ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('task.form.title_placeholder_short'))
                                                ->live(onBlur: true)
                                                ->columnSpanFull()
                                                ->extraAttributes(['class' => 'no-repeater-collapse-toolbar'])
                                                ->afterStateUpdated(function ($state, Forms\Set $set, ?Task $record) {
                                                    // Automatically enable toggle when extra_information items are added
                                                    if (! empty($state) && is_array($state)) {
                                                        $set('enable_additional_information', true);
                                                    } elseif (empty($state)) {
                                                        // For issue tracker tasks, preserve the extra_information even when empty
                                                        // This prevents reporter data from being lost when editing
                                                        if ($record && $record->tracking_token) {
                                                            // Keep the toggle enabled for issue tracker tasks
                                                            $set('enable_additional_information', true);
                                                        } else {
                                                            // Disable toggle when all items are removed for regular tasks
                                                            $set('enable_additional_information', false);
                                                        }
                                                    }
                                                }),
                                        ]),

                                    // -----------------------------
                                    // Activity Log
                                    // -----------------------------
                                    Forms\Components\Tabs\Tab::make(__('task.form.activity_log'))
                                        ->badge(function ($record) {
                                            if (! $record instanceof Task) {
                                                return null;
                                            }

                                            $activityCount = \Spatie\Activitylog\Models\Activity::where('subject_type', Task::class)
                                                ->where('subject_id', $record->id)
                                                ->where('log_name', 'Tasks')
                                                ->count();

                                            return $activityCount ?: null;
                                        })
                                        ->schema([

                                            // Activity log entries in a compact list
                                            Forms\Components\ViewField::make('activity_log_entries')
                                                ->view('filament.components.task-activity-log')
                                                ->viewData(function ($record) {
                                                    if (! $record instanceof Task) {
                                                        return ['activities' => []];
                                                    }

                                                    // Get all activities for this task
                                                    $activities = \Spatie\Activitylog\Models\Activity::with(['causer'])
                                                        ->where('subject_type', Task::class)
                                                        ->where('subject_id', $record->id)
                                                        ->where('log_name', 'Tasks')
                                                        ->orderBy('created_at', 'desc')
                                                        ->get()
                                                        ->map(function ($activity) use ($record) {
                                                            $activityData = [
                                                                'id' => $activity->id,
                                                                'description' => $activity->description,
                                                                'properties' => $activity->properties,
                                                                'created_at' => $activity->created_at,
                                                                'causer_name' => $activity->causer?->username ?? $activity->causer?->name ?? 'System',
                                                                'causer_id' => $activity->causer_id,
                                                            ];

                                                            // For 'created' or 'Task created' activities, check if it's an issue tracker creation
                                                            if ($activity->description === 'created' || $activity->description === 'Task created') {
                                                                $attributes = $activity->properties->get('attributes', []);
                                                                $status = $attributes['status'] ?? null;

                                                                // Check the actual task record for tracking_token since it might not be in activity log
                                                                $trackingToken = $record->tracking_token ?? null;
                                                                $extraInformation = $attributes['extra_information'] ?? [];
                                                                $projectIds = $attributes['project'] ?? [];

                                                                // Check if this is an issue tracker creation
                                                                // Use task record status and tracking_token since activity log might not have tracking_token
                                                                if (($status === 'issue_tracker' || $record->status === 'issue_tracker') && ! empty($trackingToken)) {
                                                                    // Extract reporter information from extra_information
                                                                    $reporterName = null;
                                                                    $reporterEmail = null;
                                                                    if (is_array($extraInformation)) {
                                                                        foreach ($extraInformation as $item) {
                                                                            if (isset($item['title']) && isset($item['value'])) {
                                                                                if ($item['title'] === 'Reporter Name') {
                                                                                    $reporterName = $item['value'];
                                                                                } elseif ($item['title'] === 'Reporter Email') {
                                                                                    $reporterEmail = $item['value'];
                                                                                }
                                                                            }
                                                                        }
                                                                    }

                                                                    // Get issue tracker code from project
                                                                    $issueTrackerCode = null;
                                                                    if (! empty($projectIds) && is_array($projectIds) && ! empty($projectIds[0])) {
                                                                        $project = \App\Models\Project::find($projectIds[0]);
                                                                        if ($project) {
                                                                            $issueTrackerCode = $project->issue_tracker_code;
                                                                        }
                                                                    }

                                                                    $activityData['is_issue_tracker'] = true;
                                                                    $activityData['reporter_name'] = $reporterName;
                                                                    $activityData['reporter_email'] = $reporterEmail;
                                                                    $activityData['issue_tracker_code'] = $issueTrackerCode;
                                                                    $activityData['tracking_token'] = $trackingToken;
                                                                }
                                                            }

                                                            return $activityData;
                                                        });

                                                    return ['activities' => $activities];
                                                })
                                                ->extraAttributes([
                                                    'style' => 'max-height: 600px; overflow-y: auto; padding: 1rem;',
                                                ])
                                                ->dehydrated(false),

                                        ]),

                                ]),

                        ])
                        ->columnSpan([
                            'default' => 1,
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 1,
                            '2xl' => 3,
                        ]),

                    // Comments sidebar (right side) - spans 1 column
                    Forms\Components\Section::make(__('task.form.comments'))
                        ->schema([

                            Forms\Components\ViewField::make('task_comments')
                                ->view('filament.components.comments-sidebar-livewire-wrapper')
                                ->viewData(function ($get, $record) {
                                    return ['taskId' => $record instanceof Task ? $record->id : null];
                                })
                                ->extraAttributes([
                                    'class' => 'flex-1 flex flex-col min-h-0',
                                    'style' => 'height:100%; display:flex; flex-direction:column;',
                                ])
                                ->dehydrated(false),
                        ])
                        ->visible(fn ($record) => $record instanceof Task)
                        ->extraAttributes([
                            ' wire:ignore' => true,
                            ' style' => 'display:flex; flex-direction:column; overflow:hidden;',
                            'class' => 'comments-pane lg:sticky lg:top-24 lg:self-start h-[82vh] lg:h-[78vh] max-h-[82vh] lg:max-h-[78vh]',
                        ])
                        ->columnSpan([
                            'default' => 1,
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 1,
                            '2xl' => 2,
                        ]),

                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTasks::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }
}
