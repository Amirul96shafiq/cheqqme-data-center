<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Relaticle\Flowforge\Filament\Pages\KanbanBoardPage;

class ActionBoard extends KanbanBoardPage
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    public ?string $search = null;

    public array $assignedToFilter = [];

    public ?string $dueDatePreset = null;

    public ?string $dueDateFrom = null;

    public ?string $dueDateTo = null;

    public array $priorityFilter = [];

    public string $cardTypeFilter = 'all';

    public bool $showFeaturedImages = true;

    public bool $showOptions = false;

    public function getSubject(): Builder
    {
        $query = Task::query()
            ->with(['comments' => function ($query) {
                // Only load comment count for performance
                $query->select('task_id', 'id');
            }])
            ->withCount('comments') // Add comments_count attribute
            ->select([
                'id', 'title', 'description', 'status', 'priority', 'order_column',
                'due_date', 'assigned_to', 'client', 'project', 'document',
                'important_url', 'attachments', 'extra_information', 'tracking_token', 'created_at', 'updated_at',
            ])
            ->orderBy('order_column')
            ->limit(300); // Limit initial load to 300 tasks (Trello approach)

        // Search filtering is handled in the Livewire Kanban component to avoid remount issues

        return $query;
    }

    public function mount(): void
    {
        // Load featured images visibility preference from session
        $this->showFeaturedImages = session('action_board_show_featured_images', true);
        // Load options visibility preference from session
        $this->showOptions = session('action_board_show_options', false);

        // Initialize card type filter from URL parameter
        $typeParam = request()->query('type');
        if ($typeParam === 'task') {
            $this->cardTypeFilter = 'tasks';
        } elseif ($typeParam === 'issue') {
            $this->cardTypeFilter = 'issue_trackers';
        } else {
            $this->cardTypeFilter = 'all';
        }

        $this
            ->titleField('title')
            ->orderField('order_column')
            ->columnField('status')
            // Attributes for badge display; virtual due_date_* for static colors.
            ->cardAttributes([
                'assigned_to_username_self' => '',
                'assigned_to_username' => '',
                'assigned_to_full_username' => '',
                'all_assigned_usernames' => '',
                'assigned_to_extra_count_self' => '',
                'assigned_to_extra_count' => '',
                'due_date_red' => '',
                'due_date_yellow' => '',
                'due_date_gray' => '',
                'due_date_green' => '',
                'featured_image' => '',
                'priority_low' => '',
                'priority_medium' => '',
                'priority_high' => '',
                'message_count' => '',
                'attachment_count' => '',
                'resource_count' => '',
            ])
            ->cardAttributeColors([
                'assigned_to_username_self' => 'teal',
                'assigned_to_username' => 'gray',
                'assigned_to_extra_count_self' => 'teal',
                'assigned_to_extra_count' => 'gray',
                'due_date_red' => 'red',
                'due_date_yellow' => 'yellow',
                'due_date_gray' => 'gray',
                'due_date_green' => 'green',
                'priority_low' => 'blue',
                'priority_medium' => 'yellow',
                'priority_high' => 'red',
                'message_count' => 'gray',
                'attachment_count' => 'gray',
                'resource_count' => 'gray',
            ])
            ->cardAttributeIcons([
                'assigned_to_username_self' => 'heroicon-m-user',
                'assigned_to_username' => 'heroicon-o-user',
                'due_date_red' => 'heroicon-o-calendar',
                'due_date_yellow' => 'heroicon-o-calendar',
                'due_date_gray' => 'heroicon-o-calendar',
                'due_date_green' => 'heroicon-o-calendar',
                'message_count' => 'heroicon-o-chat-bubble-bottom-center',
                'attachment_count' => 'heroicon-o-paper-clip',
                'resource_count' => 'heroicon-o-folder',
            ])
            ->columns(Task::availableStatuses())
            ->columnColors(array_fill_keys(array_keys(Task::availableStatuses()), 'gray'))
            ->cardLabel(__('action.card_label'))
            ->pluralCardLabel(__('action.card_label_plural'));
    }

    public function createAction(Action $action): Action
    {
        // COMPLETELY REMOVED: Old buggy create action that caused position reversion
        // This will hide all "+" buttons in kanban columns
        // Users should use the header "Create Task" button instead
        return $action->hidden();
    }

    public function editAction(Action $action): Action
    {
        // Redirect to the TaskResource edit page instead of opening an edit modal on the Action Board.
        return $action
            ->icon('heroicon-o-pencil')
            ->label(__('action.modal.edit_title'))
            ->action(function (Task $record) {
                $url = TaskResource::getUrl('edit', ['record' => $record->id]);

                return redirect()->to($url);
            });
    }

    protected function taskFormSchema(Forms\Form $form, string $mode, $defaultStatus = null)
    {
        // Only allow create flow in Action Board as requested; hide edit-specific UI.
        return $form->schema([

            // Create mode, reuse the Tabs layout without the comments section
            Forms\Components\Grid::make(5)
                ->schema([

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

                                            Forms\Components\Fieldset::make('')
                                                ->schema([

                                                    Forms\Components\Grid::make(3)
                                                        ->schema([

                                                            // Attachments Toggle
                                                            Forms\Components\Toggle::make('enable_attachments')
                                                                ->label(__('task.form.enable_attachments'))
                                                                ->default(false)
                                                                ->live()
                                                                ->dehydrated(false)
                                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                                    // When toggle is disabled, clear all attachments
                                                                    if (! $state) {
                                                                        $set('attachments', []);
                                                                    }
                                                                }),

                                                            // Task Resources Tab Toggle
                                                            Forms\Components\Toggle::make('enable_task_resources')
                                                                ->label(__('task.form.enable_task_resources'))
                                                                ->default(false)
                                                                ->live()
                                                                ->dehydrated(false)
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
                                                                ->default(false)
                                                                ->live()
                                                                ->dehydrated(false)
                                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                                    // When toggle is disabled, clear all extra_information
                                                                    if (! $state) {
                                                                        $set('extra_information', []);
                                                                    }
                                                                }),

                                                        ]),

                                                ])
                                                ->columnSpanFull(),

                                            Forms\Components\Select::make('assigned_to')
                                                ->label(__('task.form.assign_to'))
                                                ->options(function () {
                                                    return User::withTrashed()
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
                                                        ->displayFormat('j/n/y')
                                                        ->default(now()->toDateString())
                                                        ->nullable(),

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
                                                        ->searchable()
                                                        ->default($defaultStatus)
                                                        ->required(),

                                                    Forms\Components\Select::make('priority')
                                                        ->label(__('task.form.priority'))
                                                        ->options([
                                                            'low' => __('task.priority.low'),
                                                            'medium' => __('task.priority.medium'),
                                                            'high' => __('task.priority.high'),
                                                        ])
                                                        ->default('low')
                                                        ->searchable()
                                                        ->nullable(),
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
                                                    $remaining = 700 - mb_strlen($decoded);

                                                    return __('task.edit.description_helper', ['count' => $remaining]);
                                                })
                                                ->rule(function (Forms\Get $get): Closure {
                                                    return function (string $attribute, $value, Closure $fail) {
                                                        $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                        if (mb_strlen($textOnly) > 700) {
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

                                            return $client + count($project) + count($document) + count($importantUrl) ?: null;
                                        })
                                        ->visible(fn (Forms\Get $get) => (bool) $get('enable_task_resources'))
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
                                                        ->helperText(__('task.form.document_helper'))
                                                        ->options(function (Forms\Get $get) {
                                                            // If no projects are selected, return an empty array
                                                            $selectedProjects = $get('project') ?? [];
                                                            $clientId = $get('client');

                                                            if (empty($selectedProjects) && ! $clientId) {
                                                                return [];
                                                            }

                                                            if (empty($selectedProjects)) {
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
                                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                            // Automatically enable toggle when documents are selected
                                                            $selectedDocuments = $state ?? [];
                                                            if (! empty($selectedDocuments) && is_array($selectedDocuments)) {
                                                                $set('enable_task_resources', true);
                                                            }
                                                        })
                                                        ->helperText(__('task.form.document_helper')),

                                                    // Important URLs
                                                    Forms\Components\Select::make('important_url')
                                                        ->label(__('task.form.important_url'))
                                                        ->helperText(__('task.form.important_url_helper'))
                                                        ->options(function (Forms\Get $get) {
                                                            // If no client is selected, return an empty array
                                                            $clientId = $get('client');
                                                            if (! $clientId) {
                                                                return [];
                                                            }

                                                            return \App\Models\ImportantUrl::whereHas('project', function ($query) use ($clientId) {
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
                                                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                            // Automatically enable toggle when important URLs are selected
                                                            $selectedUrls = $state ?? [];
                                                            if (! empty($selectedUrls) && is_array($selectedUrls)) {
                                                                $set('enable_task_resources', true);
                                                            }
                                                        })
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
                                        ->visible(fn (Forms\Get $get) => (bool) $get('enable_additional_information'))
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
                                                        ->rule(function (Forms\Get $get): Closure {
                                                            return function (string $attribute, $value, Closure $fail) {
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
                                                ->live(onBlur: true)
                                                ->columnSpanFull()
                                                ->extraAttributes(['class' => 'no-repeater-collapse-toolbar'])
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                                    // Automatically enable toggle when extra_information items are added
                                                    if (! empty($state) && is_array($state)) {
                                                        $set('enable_additional_information', true);
                                                    } elseif (empty($state)) {
                                                        // Disable toggle when all items are removed
                                                        $set('enable_additional_information', false);
                                                    }
                                                }),

                                        ]),

                                ]),

                        ]),

                ])
                ->visible($mode === 'create'),

        ]);

    }

    /**
     * Detects the appropriate Kanban column for task creation based on request parameters or component calls.
     *
     * @return string|null Returns the column key (e.g., 'todo', 'in_progress', etc.) if detected, or null if not found.
     */
    protected function detectCreateColumn(): ?string
    {
        $valid = array_keys(Task::availableStatuses());
        $calls = request()->input('components.0.calls');
        if (is_array($calls)) {
            foreach (array_reverse($calls) as $call) {
                if (($call['method'] ?? null) === 'mountAction') {
                    $params = $call['params'] ?? [];
                    if (($params[0] ?? null) === 'create') {
                        $column = $params[1]['column'] ?? null;
                        if (is_string($column) && in_array($column, $valid)) {
                            return $column;
                        }
                    }
                }
            }
        }
        // Fallback minimal: explicit query param
        $direct = request()->get('column');
        if (is_string($direct) && in_array($direct, $valid)) {
            return $direct;
        }

        return null;
    }

    /**
     * Use the custom KanbanBoard Livewire component (with activity logging).
     */
    protected function getKanbanBoardComponent(): string
    {
        return \App\Http\Livewire\Relaticle\Flowforge\KanbanBoard::class;
    }

    /**
     * Override getAdapter to pass search term
     */
    public function getAdapter(): \Relaticle\Flowforge\Contracts\KanbanAdapterInterface
    {
        // Always create a fresh adapter with current search state
        return new \Relaticle\Flowforge\Adapters\DefaultKanbanAdapter($this->getSubject(), $this->config);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('action.navigation.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('action.navigation.label');
    }

    public static function getNavigationBadge(): ?string
    {
        // Badge is now handled by custom JavaScript for better collapsed sidebar support
        return null;
    }

    /**
     * Get all available task statuses dynamically.
     * This method serves as a single source of truth for task statuses.
     *
     * @return array<string, string> Array of status keys => translated labels
     */
    protected function getAvailableStatuses(): array
    {
        return Task::availableStatuses();
    }

    protected function getHeaderActions(): array
    {
        $statuses = Task::availableStatuses();

        $actions = [];

        // Create a group action with dropdown for each status
        foreach ($statuses as $statusKey => $statusLabel) {
            $actions[] = Action::make("createTask_{$statusKey}")
                ->label($statusLabel)
                ->color('primary')
                ->modalHeading(__('action.modal.create_title'))
                ->modalWidth('5xl')
                ->form(function (Forms\Form $form) use ($statusKey) {
                    return $this->taskFormSchema($form, 'create', $statusKey);
                })
                ->action(function (array $data) {
                    $task = Task::create($data);
                    $task->update(['order_column' => Task::max('order_column') + 1]);

                    Notification::make()
                        ->title(__('action.notifications.created'))
                        ->body(__('task.notifications.created_body', ['title' => $task->title]))
                        ->icon('heroicon-o-check-circle')
                        ->success()
                        ->send();

                    // Refresh the entire ActionBoard page to show the new task
                    $this->redirect(static::getUrl());
                });
        }

        return [

            ActionGroup::make($actions)
                ->label(__('action.modal.create_title'))
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->size('lg')
                ->button(),

            Action::make('toggleShowOptions')
                ->label(fn () => $this->showOptions ? __('action.hide_options') : __('action.show_options'))
                ->icon(fn () => $this->showOptions ? 'heroicon-o-chevron-down' : 'heroicon-o-chevron-up')
                ->color('gray')
                ->size('lg')
                ->button()
                ->extraAttributes(['style' => 'margin-right: 1px;'])
                ->action(function () {
                    $this->toggleShowOptions();
                }),

        ];
    }

    public function getTitle(): string
    {
        return __('action.title');
    }

    public function updatedSearch(): void
    {
        // Dispatch unified filter event that includes both search and assigned to filter
        $this->dispatchUnifiedFilter();
    }

    public function clearSearch(): void
    {
        $this->search = null;
        $this->dispatchUnifiedFilter();
    }

    public function updatedAssignedToFilter(): void
    {
        // Dispatch unified filter event that includes both search and assigned to filter
        $this->dispatchUnifiedFilter();
    }

    public function updatedDueDatePreset(): void
    {
        $this->dispatchUnifiedFilter();
    }

    public function updatedDueDateFrom(): void
    {
        $this->dispatchUnifiedFilter();
    }

    public function updatedDueDateTo(): void
    {
        $this->dispatchUnifiedFilter();
    }

    public function updatedPriorityFilter(): void
    {
        $this->dispatchUnifiedFilter();
    }

    public function updatedCardTypeFilter(): void
    {
        $this->dispatchUnifiedFilter();
    }

    public function toggleFeaturedImages(): void
    {
        $this->showFeaturedImages = ! $this->showFeaturedImages;
        session(['action_board_show_featured_images' => $this->showFeaturedImages]);
        $this->dispatch('featured-images-visibility-changed', visible: $this->showFeaturedImages);
    }

    public function toggleShowOptions(): void
    {
        $this->showOptions = ! $this->showOptions;
        session(['action_board_show_options' => $this->showOptions]);
    }

    public function clearFilter(): void
    {
        $this->assignedToFilter = [];
        $this->dueDatePreset = null;
        $this->dueDateFrom = null;
        $this->dueDateTo = null;
        $this->priorityFilter = [];
        $this->cardTypeFilter = 'all';
        $this->dispatchUnifiedFilter();
    }

    public function getEmptyColumnText(): string
    {
        return __('flowforge.empty_column', ['cardLabel' => strtolower($this->config->getPluralCardLabel())]);
    }

    private function dispatchUnifiedFilter(): void
    {
        // Dispatch unified filter event that includes search, assigned to, and due date filters
        // \Log::info('Dispatching unified filter', [
        //     'search' => $this->search,
        //     'assignedTo' => $this->assignedToFilter,
        //     'dueDate' => [
        //         'preset' => $this->dueDatePreset,
        //         'from' => $this->dueDateFrom,
        //         'to' => $this->dueDateTo,
        //     ],
        // ]);

        $this->dispatch('action-board-unified-filter',
            search: $this->search,
            assignedTo: $this->assignedToFilter,
            dueDate: [
                'preset' => $this->dueDatePreset,
                'from' => $this->dueDateFrom,
                'to' => $this->dueDateTo,
            ],
            priority: $this->priorityFilter,
            cardType: $this->cardTypeFilter
        );
    }

    /**
     * Move task to the top of its column
     */
    public function moveToTop(int $taskId): void
    {
        if ($taskId <= 0) {
            Notification::make()
                ->title(__('action.notifications.move_failed'))
                ->body(__('action.notifications.invalid_task_id'))
                ->danger()
                ->send();

            return;
        }

        $task = Task::findOrFail($taskId);
        $status = $task->status;

        // Get all tasks in the same column ordered by order_column
        $tasksInColumn = Task::where('status', $status)
            ->where('id', '!=', $taskId)
            ->orderBy('order_column')
            ->get();

        // Set this task's order to 0, then shift all others up by 1
        DB::transaction(function () use ($task, $tasksInColumn) {
            $task->update(['order_column' => 0]);

            foreach ($tasksInColumn as $otherTask) {
                $otherTask->increment('order_column');
            }
        });

        // Reorder to ensure clean sequential numbering
        $this->normalizeOrderColumn($status);

        Notification::make()
            ->title(__('action.notifications.moved_to_top'))
            ->success()
            ->send();

        // Only dispatch refreshBoard - task-moved is redundant and causes double refresh
        $this->dispatch('refreshBoard');
    }

    /**
     * Move task to the bottom of its column
     */
    public function moveToBottom(int $taskId): void
    {
        if ($taskId <= 0) {
            Notification::make()
                ->title(__('action.notifications.move_failed'))
                ->body(__('action.notifications.invalid_task_id'))
                ->danger()
                ->send();

            return;
        }

        $task = Task::findOrFail($taskId);
        $status = $task->status;

        // Get all tasks in the same column ordered by order_column
        $tasksInColumn = Task::where('status', $status)
            ->where('id', '!=', $taskId)
            ->orderBy('order_column')
            ->get();

        // Set this task's order to max + 1
        DB::transaction(function () use ($task, $tasksInColumn) {
            $maxOrder = $tasksInColumn->max('order_column') ?? 0;
            $task->update(['order_column' => $maxOrder + 1]);
        });

        // Reorder to ensure clean sequential numbering
        $this->normalizeOrderColumn($status);

        Notification::make()
            ->title(__('action.notifications.moved_to_bottom'))
            ->success()
            ->send();

        // Only dispatch refreshBoard - task-moved is redundant and causes double refresh
        $this->dispatch('refreshBoard');
    }

    /**
     * Move task up by one position
     */
    public function moveUpOne(int $taskId): void
    {
        if ($taskId <= 0) {
            Notification::make()
                ->title(__('action.notifications.move_failed'))
                ->body(__('action.notifications.invalid_task_id'))
                ->danger()
                ->send();

            return;
        }

        $task = Task::findOrFail($taskId);
        $status = $task->status;
        $currentOrder = $task->order_column;

        // Find the task immediately above this one
        $taskAbove = Task::where('status', $status)
            ->where('order_column', '<', $currentOrder)
            ->orderByDesc('order_column')
            ->first();

        if ($taskAbove) {
            DB::transaction(function () use ($task, $taskAbove) {
                $tempOrder = $taskAbove->order_column;
                $taskAbove->update(['order_column' => $task->order_column]);
                $task->update(['order_column' => $tempOrder]);
            });

            Notification::make()
                ->title(__('action.notifications.moved_up'))
                ->success()
                ->send();

            // Only dispatch refreshBoard - task-moved is redundant and causes double refresh
            $this->dispatch('refreshBoard');
        }
    }

    /**
     * Move task down by one position
     */
    public function moveDownOne(int $taskId): void
    {
        if ($taskId <= 0) {
            Notification::make()
                ->title(__('action.notifications.move_failed'))
                ->body(__('action.notifications.invalid_task_id'))
                ->danger()
                ->send();

            return;
        }

        $task = Task::findOrFail($taskId);
        $status = $task->status;
        $currentOrder = $task->order_column;

        // Find the task immediately below this one
        $taskBelow = Task::where('status', $status)
            ->where('order_column', '>', $currentOrder)
            ->orderBy('order_column')
            ->first();

        if ($taskBelow) {
            DB::transaction(function () use ($task, $taskBelow) {
                $tempOrder = $taskBelow->order_column;
                $taskBelow->update(['order_column' => $task->order_column]);
                $task->update(['order_column' => $tempOrder]);
            });

            Notification::make()
                ->title(__('action.notifications.moved_down'))
                ->success()
                ->send();

            // Only dispatch refreshBoard - task-moved is redundant and causes double refresh
            $this->dispatch('refreshBoard');
        }
    }

    /**
     * Normalize order_column values to be sequential (1, 2, 3, ...)
     */
    private function normalizeOrderColumn(string $status): void
    {
        $tasks = Task::where('status', $status)
            ->orderBy('order_column')
            ->get();

        foreach ($tasks as $index => $task) {
            $task->update(['order_column' => $index + 1]);
        }
    }
}
