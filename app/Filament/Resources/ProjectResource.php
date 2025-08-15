<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Closure;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $recordTitleAttribute = 'title'; // Use 'title' as the record title attribute

    public static function getGloballySearchableAttributes(): array // This method defines which attributes are searchable globally
    {
        return ['title', 'project_url', 'client.company_name'];
    }

    public static function getGlobalSearchResultDetails($record): array // This method defines the details shown in global search results
    {
        return [
            __('project.search.title') => $record->title,
            __('project.search.client') => optional($record->client)->company_name,
            __('project.search.project_url') => $record->project_url,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('project.section.project_info'))
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label(__('project.form.project_title'))
                                ->required()
                                ->maxLength(50),

                            Select::make('client_id')
                                ->label(__('project.form.client'))
                                ->relationship('client', 'company_name')
                                ->searchable()
                                ->preload()
                                ->nullable(),
                        ]),

                        TextInput::make('project_url')
                            ->label(__('project.form.project_url'))
                            ->url()
                            ->helperText(__('project.form.project_url_note'))
                            ->nullable(),

                        Textarea::make('description')
                            ->label(__('project.form.project_description'))
                            ->rows(3)
                            ->nullable()
                            ->maxLength(200),

                        Select::make('status')
                            ->label(__('project.form.project_status'))
                            ->options(['Planning' => __('project.form.planning'), 'In Progress' => __('project.form.in_progress'), 'Completed' => __('project.form.completed')])
                            ->default('Planning')
                            ->searchable()
                            ->required(),
                    ]),
                Section::make(__('project.section.project_extra_info'))
                    ->schema([
                        RichEditor::make('notes')
                            ->label(__('project.form.notes'))
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
                            // ->maxLength(500)
                            ->extraAttributes([
                                'style' => 'resize: vertical;',
                            ])
                            ->reactive()
                            // Character limit reactive function
                            ->helperText(function (Get $get) {
                                $raw = $get('notes') ?? '';
                                // 1. Strip all HTML tags
                                $noHtml = strip_tags($raw);
                                // 2. Decode HTML entities (e.g., &nbsp; -> actual space)
                                $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                // 3. Count as-is — includes normal spaces, line breaks, etc.
                                $remaining = 500 - mb_strlen($decoded);

                                return __(__('project.form.notes_helper', ['count' => $remaining]));
                            })
                            // Block save if over 500 visible characters
                            ->rule(function (Get $get): Closure {
                                return function (string $attribute, $value, Closure $fail) {
                                    $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                    if (mb_strlen($textOnly) > 500) {
                                        $fail(__('project.form.notes_warning'));
                                    }
                                };
                            })
                            ->nullable(),

                        Repeater::make('extra_information')
                            ->label(__('project.form.extra_information'))
                            // ->relationship('extra_information')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('project.form.extra_title'))
                                            ->maxLength(100)
                                            ->columnSpanFull(),
                                        RichEditor::make('value')
                                            ->label(__('project.form.extra_value'))
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
                                            // Character limit reactive function
                                            ->helperText(function (Get $get) {
                                                $raw = $get('value') ?? '';
                                                // 1. Strip all HTML tags
                                                $noHtml = strip_tags($raw);
                                                // 2. Decode HTML entities (e.g., &nbsp; -> actual space)
                                                $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                // 3. Count as-is — includes normal spaces, line breaks, etc.
                                                $remaining = 500 - mb_strlen($decoded);

                                                return __('project.form.notes_helper', ['count' => $remaining]);
                                            })
                                            // Block save if over 500 visible characters
                                            ->rule(function (Get $get): Closure {
                                                return function (string $attribute, $value, Closure $fail) {
                                                    $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                    if (mb_strlen($textOnly) > 500) {
                                                        $fail(__('project.form.notes_warning'));
                                                    }
                                                };
                                            })
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(12),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->addActionLabel(__('project.form.add_extra_info'))
                            ->cloneable()
                            ->reorderable()
                            ->collapsible(true)
                            ->collapsed()
                            ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                            ->live()
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Disable record URL for trashed records
            ->recordUrl(fn($record) => $record->trashed() ? null : static::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')->label(__('project.table.id'))->sortable(),
                TextColumn::make('title')->label(__('project.table.title'))->searchable()->sortable()->limit(20),
                TextColumn::make('client.company_name')->label(__('project.table.client'))->sortable()->searchable()->limit(20),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Planning' => 'primary',
                        'In Progress' => 'info',
                        'Completed' => 'success',
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'Planning' => __('project.table.planning'),
                        'In Progress' => __('project.table.in_progress'),
                        'Completed' => __('project.table.completed'),
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make(__('created_at'))->label(__('project.table.created_at'))->dateTime('j/n/y, h:i A')->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('project.table.updated_at_by'))
                    ->formatStateUsing(function ($state, $record) {
                        // Show '-' if there's no update or updated_by
                        if (
                            !$record->updated_by ||
                            $record->updated_at?->eq($record->created_at)
                        ) {
                            return '-';
                        }

                        $user = $record->updatedBy;
                        $formattedName = 'Unknown';

                        if ($user) {
                            $formattedName = $user->short_name;
                        }

                        return $state?->format('j/n/y, h:i A') . " ({$formattedName})";
                    })
                    ->sortable()
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make(__('project.filter.status'))
                    ->options([
                        'Planning' => __('project.filter.planning'),
                        'In Progress' => __('project.filter.in_progress'),
                        'Completed' => __('project.filter.completed'),
                    ]),
                TrashedFilter::make(), // To show trashed or only active
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->hidden(fn($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    ActivityLogTimelineTableAction::make('Log'),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('project.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('project.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('project.labels.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('project.navigation_group'); // Grouping projects under Data Management
    }

    public static function getNavigationSort(): ?int
    {
        return 22; // Adjust the navigation sort order as needed
    }
}
