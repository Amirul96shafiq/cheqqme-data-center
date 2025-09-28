<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use App\Models\User;
use Closure;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\Flowforge\Filament\Pages\KanbanBoardPage;

class ActionBoard extends KanbanBoardPage
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    public function getSubject(): Builder
    {
        return Task::query()
            ->with(['comments' => function ($query) {
                // Only load comment count for performance
                $query->select('task_id', 'id');
            }])
            ->withCount('comments') // Add comments_count attribute
            ->select([
                'id', 'title', 'description', 'status', 'order_column',
                'due_date', 'assigned_to', 'client', 'project', 'document',
                'important_url', 'attachments', 'extra_information', 'created_at', 'updated_at',
            ])
            ->orderBy('order_column')
            ->limit(300); // Limit initial load to 300 tasks (Trello approach)
    }

    public function mount(): void
    {
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
            ->columns([
                'todo' => __('action.status.todo'),
                'in_progress' => __('action.status.in_progress'),
                'toreview' => __('action.status.toreview'),
                'completed' => __('action.status.completed'),
                'archived' => __('action.status.archived'),
            ])
            ->columnColors([
                'todo', 'in_progress', 'toreview', 'completed', 'archived' => 'gray',
            ])
            ->cardLabel(__('action.card_label'))
            ->pluralCardLabel(__('action.card_label_plural'));
    }

    public function createAction(Action $action): Action
    {
        return $action
            ->iconButton()
            ->icon('heroicon-o-plus')
            ->modalHeading(__('action.modal.create_title'))
            ->modalWidth('3xl')
            ->form(function (Forms\Form $form) use ($action) {
                $args = method_exists($action, 'getArguments') ? $action->getArguments() : [];
                $col = $args['column'] ?? $this->detectCreateColumn();

                return $this->taskFormSchema($form, 'create', $col);
            })
            ->action(function (array $data) {
                $task = Task::create($data);
                $task->update(['order_column' => Task::max('order_column') + 1]); // Ensure new task is listed at the bottom
                Notification::make()
                    ->title(__('action.notifications.created'))
                    ->body(__('task.notifications.created_body', ['title' => $task->title]))
                    ->icon('heroicon-o-check-circle')
                    ->success()
                    ->send();

                // Dispatch task-created event for badge updates
                $this->dispatch('task-created');
            });
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
                                            Forms\Components\Grid::make(2)
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
                                                        ->searchable()
                                                        ->default($defaultStatus),
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
                                                ->rule(function (Forms\Get $get): Closure {
                                                    return function (string $attribute, $value, Closure $fail) {
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
                                                ->live()
                                                ->columnSpanFull()
                                                ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),
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
        $valid = ['todo', 'in_progress', 'toreview', 'completed', 'archived'];
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

    public function getTitle(): string
    {
        return __('action.title');
    }
}
