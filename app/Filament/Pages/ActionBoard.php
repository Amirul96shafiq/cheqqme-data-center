<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\Flowforge\Filament\Pages\KanbanBoardPage;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Get;
use Closure;

class ActionBoard extends KanbanBoardPage
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Boards';
    protected static ?string $navigationLabel = 'Action Board';
    protected static ?string $title = 'Action Board';

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
            ->descriptionField('description')
            // Provide attributes without visible labels (empty strings) so only the value shows on the badge.
            // Multiple virtual due_date_* attributes exist to allow different static colors per urgency.
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
                'todo' => 'To Do',
                'in_progress' => 'In Progress',
                'toreview' => 'To Review',
                'completed' => 'Completed',
                'archived' => 'Archived',
            ])
            ->columnColors([
                'todo' => 'gray',
                'in_progress' => 'blue',
                'toreview' => 'yellow',
                'completed' => 'green',
                'archived' => 'rose'
            ])
            ->cardLabel('Action Task')
            ->pluralCardLabel('Action Tasks');

        // JS hook removed – server-side detectCreateColumn() now provides deterministic column default.
    }

    public function createAction(Action $action): Action
    {
        return $action
            ->iconButton()
            ->icon('heroicon-o-plus')
            ->modalHeading('Create Action Task')
            ->modalWidth('3xl')
            ->form(fn(Forms\Form $form) => $this->taskFormSchema($form, 'create'))
            ->action(function (array $data) {
                $task = Task::create($data);
                $task->update(['order_column' => Task::max('order_column') + 1]); // Ensure new task is listed at the bottom
            });
    }

    public function editAction(Action $action): Action
    {
        return $action
            ->modalHeading('Edit Action Task')
            ->modalWidth('7xl') // Increased width to accommodate sidebar
            ->form(function (Forms\Form $form, Action $action) {
                return $this->taskFormSchema($form, 'edit');
            })
            ->mountUsing(function (Task $record, Forms\Form $form) {
                // Explicit fill ensures assigned_to (and others) populate reliably.
                $form->fill([
                    'title' => $record->title,
                    'description' => $record->description,
                    'assigned_to' => $record->assigned_to,
                    'status' => $record->status,
                    'due_date' => $record->due_date,
                    'extra_information' => $record->extra_information,
                ]);
            })
            ->action(function (array $data, Task $record) {
                // Update task data only (comments are handled separately)
                $record->update($data);
            });
    }

    protected function taskFormSchema(Forms\Form $form, string $mode)
    {
        return $form->schema([
            // For edit mode, use grid with sidebar layout
            Forms\Components\Grid::make(5)
                ->schema([
                    // Main content (left side) - spans 2 columns
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\Section::make('Task Information')
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->required()
                                        ->placeholder('Enter task title'),
                                    Forms\Components\Hidden::make('kanban_column_hint')
                                        ->dehydrated(false)
                                        ->afterStateHydrated(function (Forms\Components\Hidden $component) use ($mode) {
                                            if ($mode !== 'create')
                                                return;
                                            if ($col = $this->detectCreateColumn()) {
                                                $component->state($col);
                                            }
                                        }),
                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Select::make('assigned_to')
                                                ->label('Assign To')
                                                ->options(function () {
                                                    return User::withTrashed()
                                                        ->orderBy('username')
                                                        ->get()
                                                        ->mapWithKeys(fn($u) => [
                                                            $u->id => ($u->username ?: 'User #' . $u->id) . ($u->deleted_at ? ' (deleted)' : ''),
                                                        ])
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->afterStateHydrated(function (Forms\Components\Select $component, $state) {
                                                    if (is_array($state)) {
                                                        $component->state($state[0] ?? null);
                                                    }
                                                }),
                                            Forms\Components\DatePicker::make('due_date')
                                                ->label('Due Date'),
                                            Forms\Components\Select::make('status')
                                                ->label('Status')
                                                ->options([
                                                    'todo' => 'To Do',
                                                    'in_progress' => 'In Progress',
                                                    'toreview' => 'To Review',
                                                    'completed' => 'Completed',
                                                    'archived' => 'Archived',
                                                ])
                                                ->default(function () use ($mode) {
                                                    if ($mode === 'edit')
                                                        return null;
                                                    $col = $this->detectCreateColumn();
                                                    if (is_string($col) && in_array($col, ['todo', 'in_progress', 'toreview', 'completed', 'archived'])) {
                                                        return $col;
                                                    }
                                                    return 'todo';
                                                })
                                                ->searchable(),
                                        ]),
                                    Forms\Components\RichEditor::make('description')
                                        ->label('Description')
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
                                ]),
                            Forms\Components\Section::make('Task Additional Information')
                                ->schema([
                                    Forms\Components\Repeater::make('extra_information')
                                        ->label('Extra Information')
                                        ->schema([
                                            Forms\Components\Grid::make()
                                                ->schema([
                                                    Forms\Components\TextInput::make('title')
                                                        ->label('Title')
                                                        ->maxLength(100)
                                                        ->columnSpanFull(),
                                                    Forms\Components\RichEditor::make('value')
                                                        ->label(__('Value'))
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
                                                        ->nullable()
                                                        ->columnSpanFull(),
                                                ])
                                        ])
                                        ->defaultItems(1)
                                        ->addActionLabel(__('client.form.add_extra_info'))
                                        ->cloneable()
                                        ->reorderable()
                                        ->collapsible(true)
                                        ->collapsed()
                                        ->itemLabel(fn(array $state): string => !empty($state['title']) ? $state['title'] : 'Title goes here')
                                        ->live()
                                        ->columnSpanFull()
                                        ->extraAttributes(['class' => 'no-repeater-collapse-toolbar'])
                                ])
                                ->collapsible()
                                ->collapsed(),
                        ])
                        ->columnSpan(3),

                    // Comments sidebar (right side) - spans 1 column
                    Forms\Components\Section::make('Comments')
                        ->schema([
                            Forms\Components\ViewField::make('task_comments')
                                ->view('filament.components.comments-sidebar-livewire-wrapper')
                                ->viewData(function ($get, $record) {
                                    return ['taskId' => $record instanceof Task ? $record->id : null];
                                })
                                ->extraAttributes([
                                    // Allow the Livewire root to stretch and enable internal flex scrolling
                                    'class' => 'flex-1 flex flex-col min-h-0',
                                    'style' => 'height:100%; display:flex; flex-direction:column;'
                                ])
                                ->dehydrated(false),
                        ])
                        ->extraAttributes([
                            // Fixed height; internal Livewire component handles its own scrolling; hide any accidental overflow outside border.
                            'style' => 'height:68vh; max-height:68vh; position:sticky; top:3vh; display:flex; flex-direction:column; align-self:flex-start; overflow:hidden;',
                            'class' => 'comments-pane'
                        ])
                        ->columnSpan(2),
                ])
                ->visible($mode === 'edit'),

            // For create mode, use the original single-column layout
            Forms\Components\Section::make('Task Information')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->placeholder('Enter task title'),
                    Forms\Components\Hidden::make('kanban_column_hint')
                        ->dehydrated(false)
                        ->afterStateHydrated(function (Forms\Components\Hidden $component) use ($mode) {
                            if ($mode !== 'create')
                                return;
                            if ($col = $this->detectCreateColumn()) {
                                $component->state($col);
                            }
                        }),
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\Select::make('assigned_to')
                                ->label('Assign To')
                                ->options(function () {
                                    return User::withTrashed()
                                        ->orderBy('username')
                                        ->get()
                                        ->mapWithKeys(fn($u) => [
                                            $u->id => ($u->username ?: 'User #' . $u->id) . ($u->deleted_at ? ' (deleted)' : ''),
                                        ])
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->afterStateHydrated(function (Forms\Components\Select $component, $state) {
                                    if (is_array($state)) {
                                        $component->state($state[0] ?? null);
                                    }
                                }),
                            Forms\Components\DatePicker::make('due_date')
                                ->label('Due Date'),
                            Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'todo' => 'To Do',
                                    'in_progress' => 'In Progress',
                                    'toreview' => 'To Review',
                                    'completed' => 'Completed',
                                    'archived' => 'Archived',
                                ])
                                ->default(function () use ($mode) {
                                    if ($mode === 'edit')
                                        return null;
                                    $col = $this->detectCreateColumn();
                                    if (is_string($col) && in_array($col, ['todo', 'in_progress', 'toreview', 'completed', 'archived'])) {
                                        return $col;
                                    }
                                    return 'todo';
                                })
                                ->searchable(),
                        ]),
                    Forms\Components\RichEditor::make('description')
                        ->label('Description')
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

            Forms\Components\Section::make('Task Additional Information')
                ->schema([
                    Forms\Components\Repeater::make('extra_information')
                        ->label('Extra Information')
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->label('Title')
                                        ->maxLength(100)
                                        ->columnSpanFull(),
                                    Forms\Components\RichEditor::make('value')
                                        ->label(__('Value'))
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
                                        ->nullable()
                                        ->columnSpanFull(),
                                ])
                        ])
                        ->defaultItems(1)
                        ->addActionLabel(__('client.form.add_extra_info'))
                        ->cloneable()
                        ->reorderable()
                        ->collapsible(true)
                        ->collapsed()
                        ->itemLabel(fn(array $state): string => !empty($state['title']) ? $state['title'] : 'Title goes here')
                        ->live()
                        ->columnSpanFull()
                        ->extraAttributes(['class' => 'no-repeater-collapse-toolbar'])
                ])
                ->collapsible()
                ->collapsed()
                ->visible($mode === 'create'),
        ]);
    }

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
        if (is_string($direct) && in_array($direct, $valid))
            return $direct;
        return null;
    }
}
