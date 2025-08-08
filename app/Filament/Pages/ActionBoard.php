<?php

namespace App\Filament\Pages;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Relaticle\Flowforge\Filament\Pages\KanbanBoardPage;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Get;
use Closure;

class ActionBoard extends KanbanBoardPage
{
    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';
    protected static ?string $navigationGroup = 'Boards';

    protected static ?string $navigationLabel = 'Action Board';
    protected static ?string $title = 'Task Board';

    public function getSubject(): Builder
    {
        return Task::query();
    }

    public function mount(): void
    {
        $this
            ->titleField('title')
            ->orderField('sort_order')
            ->columnField('status')
            ->descriptionField('description')
            ->cardAttributes([
                'due_date' => 'Due Date',
                'assigned_to' => 'Assigned To',
            ])
            ->cardAttributeColors([
                'due_date' => 'gray',
                'assigned_to' => 'gray',
            ])
            ->cardAttributeIcons([
                'due_date' => 'heroicon-o-calendar',
                'assigned_to' => 'heroicon-o-user',
            ])
            ->columns([
                'todo' => 'To Do',
                'in_progress' => 'In Progress',
                'toreview' => 'To Review',
                'completed' => 'Completed',
                'archived' => 'Archived',
            ])
            ->orderField('order_column')
            ->columnColors([
                'todo' => 'gray',
                'in_progress' => 'blue',
                'toreview' => 'yellow',
                'completed' => 'green',
                'archived' => 'rose'
            ])
            ->cardLabel('Action Task')
            ->pluralCardLabel('Action Tasks');
    }

    public function createAction(Action $action): Action
    {
        return $action
            ->iconButton()
            ->icon('heroicon-o-plus')
            ->modalHeading('Create Action Task')
            ->modalWidth('xl')
            ->form(function (Forms\Form $form) {
                return $form->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->placeholder('Enter task title')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('assigned_to')
                        ->label('Assign To')
                        ->options(User::all()->pluck('username', 'id'))
                        ->searchable()
                        ->columnSpanFull(),
                    Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Repeater::make('extra_information')
                        ->label('Extra Information')
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->label('Title')
                                        ->required()
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
                                            'bulletList',
                                            'codeBlock',
                                        ])
                                        ->extraAttributes([
                                            'style' => 'resize: vertical;',
                                        ])
                                        ->reactive()
                                        //Character limit reactive function
                                        ->helperText(function (Get $get) {
                                            $raw = $get('value') ?? '';
                                            // 1. Strip all HTML tags
                                            $noHtml = strip_tags($raw);
                                            // 2. Decode HTML entities (e.g., &nbsp; -> actual space)
                                            $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                            // 3. Count as-is — includes normal spaces, line breaks, etc.
                                            $remaining = 500 - mb_strlen($decoded);
                                            return __("task.form.notes_helper", ['count' => $remaining]);
                                        })
                                        // Block save if over 500 visible characters
                                        ->rule(function (Get $get): Closure {
                                            return function (string $attribute, $value, Closure $fail) {
                                                $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                if (mb_strlen($textOnly) > 500) {
                                                    $fail(__("task.form.notes_warning"));
                                                }
                                            };
                                        })
                                        ->nullable()
                                        ->columnSpanFull(),
                                ])
                        ])
                ]);
            });
    }

    public function editAction(Action $action): Action
    {
        return $action
            ->modalHeading('Edit Action Task')
            ->modalWidth('xl')
            ->form(function (Forms\Form $form) {
                return $form->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->placeholder('Enter task title')
                        ->columnSpanFull(),
                    Forms\Components\Select::make('assigned_to')
                        ->label('Assign To')
                        ->options(User::all()->pluck('username', 'id'))
                        ->searchable()
                        ->columnSpanFull(),
                    Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date')
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Repeater::make('extra_information')
                        ->label('Extra Information')
                        ->schema([
                            Forms\Components\Grid::make()
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->label('Title')
                                        ->required()
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
                                            'bulletList',
                                            'codeBlock',
                                        ])
                                        ->extraAttributes([
                                            'style' => 'resize: vertical;',
                                        ])
                                        ->reactive()
                                        //Character limit reactive function
                                        ->helperText(function (Get $get) {
                                            $raw = $get('value') ?? '';
                                            // 1. Strip all HTML tags
                                            $noHtml = strip_tags($raw);
                                            // 2. Decode HTML entities (e.g., &nbsp; -> actual space)
                                            $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                            // 3. Count as-is — includes normal spaces, line breaks, etc.
                                            $remaining = 500 - mb_strlen($decoded);
                                            return __("task.form.notes_helper", ['count' => $remaining]);
                                        })
                                        // Block save if over 500 visible characters
                                        ->rule(function (Get $get): Closure {
                                            return function (string $attribute, $value, Closure $fail) {
                                                $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                if (mb_strlen($textOnly) > 500) {
                                                    $fail(__("task.form.notes_warning"));
                                                }
                                            };
                                        })
                                        ->nullable()
                                        ->columnSpanFull(),
                                ])
                        ])
                ]);
            });
    }
}
