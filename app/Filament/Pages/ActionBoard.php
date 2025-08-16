<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\User;
use Closure;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\Flowforge\Filament\Pages\KanbanBoardPage;

class ActionBoard extends KanbanBoardPage
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationGroup = null;

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    // Removed native Filament navigation badge methods; using fully custom persistent badge.

    public function getSubject(): Builder
    {
        return Task::query();
    }

    public function mount(): void
    {
        $this
            ->titleField('title')
            ->orderField('order_column')
            ->columnField('status')
            // Attributes for badge display; virtual due_date_* for static colors.
            ->cardAttributes([
                'due_date_red' => '',
                'due_date_yellow' => '',
                'due_date_gray' => '',
                'due_date_green' => '',
                'assigned_to_username_self' => '',
                'assigned_to_username' => '',
            ])
            ->cardAttributeColors([
                'due_date_red' => 'red',
                'due_date_yellow' => 'yellow',
                'due_date_gray' => 'gray',
                'due_date_green' => 'green',
                'assigned_to_display' => fn($record) => $record->assigned_to_display_color,
            ])
            ->cardAttributeIcons([
                'due_date_red' => 'heroicon-o-calendar',
                'due_date_yellow' => 'heroicon-o-calendar',
                'due_date_gray' => 'heroicon-o-calendar',
                'due_date_green' => 'heroicon-o-calendar',
                'assigned_to_display' => fn($record) => $record->assigned_to_display_icon,
            ])
            ->cardAttributeColors([
                'due_date_red' => 'red',
                'due_date_yellow' => 'yellow',
                'due_date_gray' => 'gray',
                'due_date_green' => 'green',
                'assigned_to_badge' => 'cyan', // Use cyan for self, gray for others in accessor
            ])
            ->cardAttributeIcons([
                'due_date_red' => 'heroicon-o-calendar',
                'due_date_yellow' => 'heroicon-o-calendar',
                'due_date_gray' => 'heroicon-o-calendar',
                'due_date_green' => 'heroicon-o-calendar',
                'assigned_to_badge' => 'heroicon-o-user', // Use different icon in accessor if needed
            ])
            ->cardAttributeColors([
                'due_date_red' => 'red',
                'due_date_yellow' => 'yellow',
                'due_date_gray' => 'gray',
                'due_date_green' => 'green',
                'assigned_to_username_self' => 'cyan',
                'assigned_to_username' => 'gray',
            ])
            ->cardAttributeIcons([
                'due_date_red' => 'heroicon-o-calendar',
                'due_date_yellow' => 'heroicon-o-calendar',
                'due_date_gray' => 'heroicon-o-calendar',
                'due_date_green' => 'heroicon-o-calendar',
                'assigned_to_username_self' => 'heroicon-m-user',
                'assigned_to_username' => 'heroicon-o-user',
            ])
            ->columns([
                'todo' => __('action.status.todo'),
                'in_progress' => __('action.status.in_progress'),
                'toreview' => __('action.status.toreview'),
                'completed' => __('action.status.completed'),
                'archived' => __('action.status.archived'),
            ])
            ->columnColors([
                'todo' => 'gray',
                'in_progress' => 'blue',
                'toreview' => 'yellow',
                'completed' => 'green',
                'archived' => 'rose',
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
                    ->success()
                    ->send();
            });
    }

    public function editAction(Action $action): Action
    {
        return $action
            ->modalHeading(__('action.modal.edit_title'))
            ->modalWidth('7xl') // Increased width to accommodate sidebar
            ->form(function (Forms\Form $form, Action $action) {
                $args = method_exists($action, 'getArguments') ? $action->getArguments() : [];
                $col = $args['column'] ?? $this->detectCreateColumn();
                if (is_string($col) && in_array($col, ['todo', 'in_progress', 'toreview', 'completed', 'archived'])) {
                    $form->fill(['status' => $col]);
                }

                return $this->taskFormSchema($form, 'edit');
            })
            ->action(function (array $data, Task $record) {
                // Update task data only (comments are handled separately)
                $record->update($data);
                Notification::make()
                    ->title(__('action.notifications.updated'))
                    ->success()
                    ->send();
            });
    }

    protected function taskFormSchema(Forms\Form $form, string $mode, $defaultStatus = null)
    {
        return $form->schema([
            // For edit mode, use grid with sidebar layout
            Forms\Components\Grid::make(5)
                ->schema([
                    // Main content (left side) - spans 2 columns
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\Section::make(__('action.form.task_information'))
                                ->schema([
                                    Forms\Components\Hidden::make('id')
                                        ->disabled() // not user editable
                                        ->visible(false),
                                    Forms\Components\TextInput::make('title')
                                        ->label(__('action.form.title'))
                                        ->required()
                                        ->placeholder(__('action.form.title_placeholder')),
                                    Forms\Components\Hidden::make('kanban_column_hint')
                                        ->dehydrated(false)
                                        ->afterStateHydrated(function (Forms\Components\Hidden $component) {
                                            // Removed unused kanban_column_hint hidden field
                                        }),
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            // Updated assigned_to select for edit layout
                                            Forms\Components\Select::make('assigned_to')
                                                ->label(__('action.form.assign_to'))
                                                ->options(function () {
                                                    return User::withTrashed()
                                                        ->orderBy('username')
                                                        ->get()
                                                        ->mapWithKeys(fn($u) => [
                                                            $u->id => ($u->username ?: __('action.form.user_with_id', ['id' => $u->id])) . ($u->deleted_at ? __('action.form.deleted_suffix') : ''),
                                                        ])
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->nullable()
                                                ->formatStateUsing(fn($state, ?Task $record) => $record?->assigned_to)
                                                ->default(fn(?Task $record) => $record?->assigned_to)
                                                ->dehydrated(),
                                            Forms\Components\DatePicker::make('due_date')
                                                ->label(__('action.form.due_date')),
                                            Forms\Components\Select::make('status')
                                                ->label(__('action.form.status'))
                                                ->options([
                                                    'todo' => __('action.status.todo'),
                                                    'in_progress' => __('action.status.in_progress'),
                                                    'toreview' => __('action.status.toreview'),
                                                    'completed' => __('action.status.completed'),
                                                    'archived' => __('action.status.archived'),
                                                ])
                                                ->searchable()
                                                ->default($defaultStatus),
                                        ]),
                                    Forms\Components\RichEditor::make('description')
                                        ->label(__('action.form.description'))
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
                                        ->helperText(function (Get $get) use ($mode) {
                                            $raw = $get('description') ?? '';
                                            $noHtml = strip_tags($raw);
                                            $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                            $remaining = 500 - mb_strlen($decoded);

                                            return __("action.$mode.description_helper", ['count' => $remaining]);
                                        })
                                        ->rule(function (Get $get) use ($mode): Closure {
                                            return function (string $attribute, $value, Closure $fail) use ($mode) {
                                                $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                if (mb_strlen($textOnly) > 500) {
                                                    $fail(__("action.$mode.description_warning"));
                                                }
                                            };
                                        })
                                        ->nullable()
                                        ->columnSpanFull(),

                                    Forms\Components\Fieldset::make(__('task.form.task_resources'))
                                        ->columns(1)
                                        ->schema([
                                            Forms\Components\Select::make('client')
                                                ->label(__('task.form.client'))
                                                ->options(function () {
                                                    // return \App\Models\Client::all()->pluck('name', 'id');
                                                    return \App\Models\Client::withTrashed()
                                                        ->orderBy('company_name')
                                                        ->get()
                                                        ->mapWithKeys(fn($c) => [
                                                            $c->id => ($c->company_name ?: 'Company #' . $c->id) . ($c->deleted_at ? ' (deleted)' : ''),
                                                        ])
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->nullable()
                                                ->default(fn(?Task $record) => $record?->client)
                                                ->dehydrated()
                                                ->live()
                                                ->suffixAction(
                                                    Forms\Components\Actions\Action::make('openClient')
                                                        ->icon('heroicon-o-arrow-top-right-on-square')
                                                        ->url(function (Forms\Get $get) {
                                                            $clientId = $get('client');
                                                            if (!$clientId) {
                                                                return null;
                                                            }
                                                            return \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $clientId]);
                                                        })
                                                        ->openUrlInNewTab()
                                                        ->visible(fn(Forms\Get $get) => (bool) $get('client'))
                                                )
                                                ->afterStateUpdated(function ($state, Forms\Set $set) {
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

                                                        // Auto-select all projects and documents for the client
                                                        $set('project', $projects);
                                                        $set('document', $documents);
                                                    } else {
                                                        // Clear selections when no client is selected
                                                        $set('project', []);
                                                        $set('document', []);
                                                    }
                                                }),
                                            Forms\Components\Grid::make(2)
                                                ->schema([
                                                    Forms\Components\Select::make('project')
                                                        ->label(__('task.form.project'))
                                                        ->helperText(__('task.form.project_helper'))
                                                        ->options(function (Forms\Get $get) {
                                                            $clientId = $get('client');
                                                            if (!$clientId) {
                                                                return [];
                                                            }

                                                            return \App\Models\Project::where('client_id', $clientId)
                                                                ->withTrashed()
                                                                ->orderBy('title')
                                                                ->get()
                                                                ->mapWithKeys(fn($p) => [
                                                                    $p->id => str($p->title)->limit(25) . ($p->deleted_at ? ' (deleted)' : ''),
                                                                ])
                                                                ->toArray();
                                                        })
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->nullable()
                                                        ->multiple()
                                                        ->default(fn(?Task $record) => $record?->project)
                                                        ->dehydrated(),
                                                    Forms\Components\Select::make('document')
                                                        ->label(__('task.form.document'))
                                                        ->helperText(__('task.form.document_helper'))
                                                        ->options(function (Forms\Get $get) {
                                                            $clientId = $get('client');
                                                            if (!$clientId) {
                                                                return [];
                                                            }

                                                            return \App\Models\Document::whereHas('project', function ($query) use ($clientId) {
                                                                $query->where('client_id', $clientId);
                                                            })
                                                                ->withTrashed()
                                                                ->orderBy('title')
                                                                ->get()
                                                                ->mapWithKeys(fn($d) => [
                                                                    $d->id => str($d->title)->limit(25) . ($d->deleted_at ? ' (deleted)' : ''),
                                                                ])
                                                                ->toArray();
                                                        })
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->nullable()
                                                        ->multiple()
                                                        ->default(fn(?Task $record) => $record?->document)
                                                        ->dehydrated(),
                                                ]),

                                        ]),
                                ]),
                            Forms\Components\Section::make()
                                ->heading(function (Get $get) {
                                    $count = 0;

                                    // Add count of extra_information items
                                    $extraInfo = $get('extra_information') ?? [];
                                    $count += count($extraInfo);

                                    $title = __('task.form.additional_information');
                                    $badge = '<span style="color: #FBB43E; font-weight: 700;">(' . $count . ')</span>';

                                    return new \Illuminate\Support\HtmlString($title . ' ' . $badge);
                                })
                                ->collapsible(true)
                                ->live()
                                ->schema([
                                    Forms\Components\Repeater::make('extra_information')
                                        ->label(__('action.form.extra_information'))
                                        ->schema([
                                            Forms\Components\TextInput::make('title')
                                                ->label(__('action.form.title'))
                                                ->maxLength(100)
                                                ->columnSpanFull(),
                                            Forms\Components\RichEditor::make('value')
                                                ->label(__('action.form.value'))
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
                                                ->helperText(function (Get $get) use ($mode) {
                                                    $raw = $get('value') ?? '';
                                                    $noHtml = strip_tags($raw);
                                                    $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $remaining = 500 - mb_strlen($decoded);

                                                    return __("action.$mode.extra_information_helper", ['count' => $remaining]);
                                                })
                                                ->rule(function (Get $get) use ($mode): Closure {
                                                    return function (string $attribute, $value, Closure $fail) use ($mode) {
                                                        $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                        if (mb_strlen($textOnly) > 500) {
                                                            $fail(__("action.$mode.extra_information_warning"));
                                                        }
                                                    };
                                                })
                                                ->columnSpanFull(),
                                        ])
                                        ->defaultItems(1)
                                        ->addActionLabel(__('client.form.add_extra_info'))
                                        ->cloneable()
                                        ->reorderable()
                                        ->collapsible(true)
                                        ->collapsed()
                                        ->itemLabel(fn(array $state): string => !empty($state['title']) ? $state['title'] : __('action.form.title_placeholder_short'))
                                        ->live()
                                        ->columnSpanFull()
                                        ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),
                                ]),
                        ])
                        ->columnSpan(3),

                    // Comments sidebar (right side) - spans 1 column
                    Forms\Components\Section::make(__('action.form.comments'))
                        ->schema([
                            Forms\Components\ViewField::make('task_comments')
                                ->view('filament.components.comments-sidebar-livewire-wrapper')
                                ->viewData(function ($get, $record) {
                                    return ['taskId' => $record instanceof Task ? $record->id : null];
                                })
                                ->extraAttributes([
                                    // Allow the Livewire root to stretch and enable internal flex scrolling
                                    'class' => 'flex-1 flex flex-col min-h-0',
                                    'style' => 'height:100%; display:flex; flex-direction:column;',
                                ])
                                ->dehydrated(false),
                        ])
                        ->extraAttributes([
                            // Fixed height; internal Livewire component handles its own scrolling; hide any accidental overflow outside border.
                            'style' => 'height:68vh; max-height:68vh; position:sticky; top:3vh; display:flex; flex-direction:column; align-self:flex-start; overflow:hidden;',
                            'class' => 'comments-pane',
                        ])
                        ->columnSpan(2),
                ])
                ->visible($mode === 'edit'),

            // For create mode, use the original single-column layout
            Forms\Components\Section::make(__('action.form.task_information'))
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->label(__('action.form.title'))
                        ->required()
                        ->placeholder(__('action.form.title_placeholder')),
                    Forms\Components\Hidden::make('kanban_column_hint')
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Forms\Components\Hidden $component) use ($mode) {
                            if ($mode !== 'create') {
                                return;
                            }
                            if ($col = $this->detectCreateColumn()) {
                                $component->state($col);
                            }
                        }),
                    Forms\Components\Grid::make(3)
                        ->schema([
                            // Updated assigned_to select for create layout
                            Forms\Components\Select::make('assigned_to')
                                ->label(__('action.form.assign_to'))
                                ->options(function () {
                                    return User::withTrashed()
                                        ->orderBy('username')
                                        ->get()
                                        ->mapWithKeys(fn($u) => [
                                            $u->id => ($u->username ?: __('action.form.user_with_id', ['id' => $u->id])) . ($u->deleted_at ? __('action.form.deleted_suffix') : ''),
                                        ])
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->nullable()
                                ->formatStateUsing(fn($state, ?Task $record) => $record?->assigned_to)
                                ->default(fn(?Task $record) => $record?->assigned_to)
                                ->dehydrated(),
                            Forms\Components\DatePicker::make('due_date')
                                ->label(__('action.form.due_date')),
                            Forms\Components\Select::make('status')
                                ->label(__('action.form.status'))
                                ->options([
                                    'todo' => __('action.status.todo'),
                                    'in_progress' => __('action.status.in_progress'),
                                    'toreview' => __('action.status.toreview'),
                                    'completed' => __('action.status.completed'),
                                    'archived' => __('action.status.archived'),
                                ])
                                ->default(function (Get $get) {
                                    $valid = ['todo', 'in_progress', 'toreview', 'completed', 'archived'];
                                    $status = $get('status');

                                    return (is_string($status) && in_array($status, $valid)) ? $status : 'todo';
                                })
                                ->searchable(),
                        ]),
                    Forms\Components\RichEditor::make('description')
                        ->label(__('action.form.description'))
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
                        ->helperText(function (Get $get) use ($mode) {
                            $raw = $get('description') ?? '';
                            $noHtml = strip_tags($raw);
                            $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            $remaining = 500 - mb_strlen($decoded);

                            return __("action.$mode.description_helper", ['count' => $remaining]);
                        })
                        ->rule(function (Get $get) use ($mode): Closure {
                            return function (string $attribute, $value, Closure $fail) use ($mode) {
                                $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                if (mb_strlen($textOnly) > 500) {
                                    $fail(__("action.$mode.description_warning"));
                                }
                            };
                        })
                        ->nullable()
                        ->columnSpanFull(),
                ])
                ->visible($mode === 'create'),

            Forms\Components\Section::make(__('action.form.additional_information'))
                ->schema([
                    Forms\Components\Repeater::make('extra_information')
                        ->label(__('action.form.extra_information'))
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->label(__('action.form.title'))
                                        ->maxLength(100)
                                        ->columnSpanFull(),
                                    Forms\Components\RichEditor::make('value')
                                        ->label(__('action.form.value'))
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
                                        ->helperText(function (Get $get) use ($mode) {
                                            $raw = $get('value') ?? '';
                                            $noHtml = strip_tags($raw);
                                            $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                            $remaining = 500 - mb_strlen($decoded);

                                            return __("action.$mode.extra_information_helper", ['count' => $remaining]);
                                        })
                                        ->rule(function (Get $get) use ($mode): Closure {
                                            return function (string $attribute, $value, Closure $fail) use ($mode) {
                                                $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                if (mb_strlen($textOnly) > 500) {
                                                    $fail(__("action.$mode.extra_information_warning"));
                                                }
                                            };
                                        })
                                        ->columnSpanFull(),
                                ]),
                        ])
                        ->defaultItems(1)
                        ->addActionLabel(__('client.form.add_extra_info'))
                        ->cloneable()
                        ->reorderable()
                        ->collapsible(true)
                        ->collapsed()
                        ->itemLabel(fn(array $state): string => !empty($state['title']) ? $state['title'] : __('action.form.title_placeholder_short'))
                        ->live()
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),
                ])
                ->collapsible()
                ->collapsed()
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

    public function getTitle(): string
    {
        return __('action.title');
    }
}
