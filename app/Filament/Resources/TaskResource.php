<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;

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

    /**
     * Redirect global search result to Action Board and open the selected Task.
     */
    /*
    public static function getGlobalSearchResultUrl($record): string
    {
        // Replace with your Action Board route and pass the Task ID as a query param
        return route('filament.pages.action-board', ['task' => $record->id]);
    }*/

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('task.search.status') => $record->status,
            __('task.search.due_date') => $record->due_date,
            __('task.search.assigned_to') => $record->assigned_to_username,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(5)
                ->schema([
                    // Main content (left side) - spans 2 columns
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\Section::make(__('task.form.task_information'))
                                ->schema([
                                    Forms\Components\Hidden::make('id')
                                        ->disabled()
                                        ->visible(false),
                                    Forms\Components\TextInput::make('title')
                                        ->label(__('task.form.title'))
                                        ->required()
                                        ->placeholder(__('task.form.title_placeholder'))
                                        ->columnSpanFull(),
                                    Forms\Components\Grid::make(3)
                                        ->schema([
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
                                                ->formatStateUsing(fn ($state, ?Task $record) => $record?->assigned_to)
                                                ->default(fn (?Task $record) => $record?->assigned_to)
                                                ->dehydrated(),
                                            Forms\Components\DatePicker::make('due_date')
                                                ->label(__('task.form.due_date')),
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
                                                        ->mapWithKeys(fn ($c) => [
                                                            $c->id => ($c->company_name ?: 'Company #'.$c->id).($c->deleted_at ? ' (deleted)' : ''),
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
                                                            if (! $clientId) {
                                                                return [];
                                                            }

                                                            return \App\Models\Project::where('client_id', $clientId)
                                                                ->withTrashed()
                                                                ->orderBy('title')
                                                                ->get()
                                                                ->mapWithKeys(fn ($p) => [
                                                                    $p->id => str($p->title)->limit(25).($p->deleted_at ? ' (deleted)' : ''),
                                                                ])
                                                                ->toArray();
                                                        })
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->nullable()
                                                        ->multiple()
                                                        ->default(fn (?Task $record) => $record?->project)
                                                        ->dehydrated(),
                                                    Forms\Components\Select::make('document')
                                                        ->label(__('task.form.document'))
                                                        ->helperText(__('task.form.document_helper'))
                                                        ->options(function (Forms\Get $get) {
                                                            $clientId = $get('client');
                                                            if (! $clientId) {
                                                                return [];
                                                            }

                                                            return \App\Models\Document::whereHas('project', function ($query) use ($clientId) {
                                                                $query->where('client_id', $clientId);
                                                            })
                                                                ->withTrashed()
                                                                ->orderBy('title')
                                                                ->get()
                                                                ->mapWithKeys(fn ($d) => [
                                                                    $d->id => str($d->title)->limit(25).($d->deleted_at ? ' (deleted)' : ''),
                                                                ])
                                                                ->toArray();
                                                        })
                                                        ->searchable()
                                                        ->preload()
                                                        ->native(false)
                                                        ->nullable()
                                                        ->multiple()
                                                        ->default(fn (?Task $record) => $record?->document)
                                                        ->dehydrated(),
                                                ]),

                                        ]),
                                ]),
                            Forms\Components\Section::make(__('task.form.additional_information'))
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
                                        ->cloneable()
                                        ->reorderable()
                                        ->collapsible(true)
                                        ->collapsed()
                                        ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('task.form.title_placeholder_short'))
                                        ->live()
                                        ->columnSpanFull()
                                        ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),
                                ])
                                ->collapsible()
                                ->collapsed(),
                        ])
                        ->columnSpan(3),

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
                        ->extraAttributes([
                            'style' => 'height:68vh; max-height:68vh; position:sticky; top:3vh; display:flex; flex-direction:column; align-self:flex-start; overflow:hidden;',
                            'class' => 'comments-pane',
                        ])
                        ->columnSpan(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Add your table columns here
            ])
            ->filters([
                // Add your table filters here
            ])
            ->actions([
                // Add your table actions here
            ])
            ->bulkActions([
                // Add your bulk actions here
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
