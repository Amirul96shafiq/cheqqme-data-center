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
                                                        ->default('medium'),
                                                ]),
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
                                                ->nullable(),
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

                                            return $client + count($project) + count($document) + count($importantUrl) ?: null;
                                        })
                                        ->schema([
                                            // Client
                                            Forms\Components\Select::make('client')
                                                ->label(__('task.form.client'))
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
                                                )
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
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
                                                        ->helperText(__('task.form.project_helper'))
                                                        ->options(function (Forms\Get $get) {
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
                                                        ->helperText(__('task.form.project_helper'))
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->nullable()
                                                        ->multiple()
                                                        ->default(fn (?Task $record) => $record?->project)
                                                        ->dehydrated()
                                                        ->live()
                                                        ->reactive()
                                                        ->suffixAction(
                                                            Forms\Components\Actions\Action::make('createProject')
                                                                ->icon('heroicon-o-plus')
                                                                ->url(\App\Filament\Resources\ProjectResource::getUrl('create'))
                                                                ->openUrlInNewTab()
                                                                ->label(__('task.form.create_project'))
                                                        )
                                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                            // If no projects are selected, clear all documents
                                                            $selectedProjects = $state ?? [];
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
                                                        ->helperText(__('task.form.document_helper'))
                                                        ->options(function (Forms\Get $get) {
                                                            // If no projects are selected, return an empty array
                                                            $selectedProjects = $get('project') ?? [];

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
                                                        )
                                                        ->helperText(__('task.form.document_helper')),
                                                    // Important URLs
                                                    Forms\Components\Select::make('important_url')
                                                        ->label(__('task.form.important_url'))
                                                        ->helperText(__('task.form.important_url_helper'))
                                                        ->options(function (Forms\Get $get) {
                                                            // If no client is selected, return an empty array
                                                            return \App\Models\ImportantUrl::whereHas('project', function ($query) use ($get) {
                                                                $clientId = $get('client');
                                                                if (! $clientId) {
                                                                    return $query;
                                                                }

                                                                // Get all important URLs for the client
                                                                return $query->where('client_id', $clientId);
                                                            })
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
                                                        )
                                                        ->helperText(__('task.form.important_url_helper')),

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
                                        ->schema([
                                            Forms\Components\Repeater::make('extra_information')
                                                ->label(__('task.form.extra_information'))
                                                ->schema([
                                                    Forms\Components\TextInput::make('title')
                                                        ->label(__('task.form.title'))
                                                        ->maxLength(100)
                                                        ->columnSpanFull(),
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
                                                ->collapsible(true)
                                                ->collapsed()
                                                ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('task.form.title_placeholder_short'))
                                                ->live()
                                                ->columnSpanFull()
                                                ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),
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
                                                        ->map(function ($activity) {
                                                            return [
                                                                'id' => $activity->id,
                                                                'description' => $activity->description,
                                                                'properties' => $activity->properties,
                                                                'created_at' => $activity->created_at,
                                                                'causer_name' => $activity->causer?->username ?? $activity->causer?->name ?? 'System',
                                                                'causer_id' => $activity->causer_id,
                                                            ];
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
