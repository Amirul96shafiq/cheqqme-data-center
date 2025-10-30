<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportantUrlResource\Pages;
use App\Filament\Resources\ImportantUrlResource\RelationManagers\ImportantUrlActivityLogRelationManager;
use App\Helpers\ClientFormatter;
use App\Models\ImportantUrl;
use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class ImportantUrlResource extends Resource
{
    protected static ?string $model = ImportantUrl::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $recordTitleAttribute = 'title'; // Use 'title' as the record title attribute

    public static function getGloballySearchableAttributes(): array // This method defines which attributes are searchable globally
    {
        return ['title', 'url', 'project.title', 'client.company_name'];
    }

    public static function getGlobalSearchResultDetails($record): array // This method defines the details shown in global search results
    {
        return [
            __('importanturl.search.project') => $record->project?->title ?? 'N/A',
            __('importanturl.search.client') => $record->client?->company_name ?? 'N/A',
            __('importanturl.search.url') => $record->url ? (strlen($record->url) > 40 ? substr($record->url, 0, 40).'...' : $record->url) : '-',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('importanturl.section.important_url_info'))->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                        '2xl' => 3,
                    ])->schema([
                        TextInput::make('title')->label(__('importanturl.form.important_url_title'))->required()->maxLength(50),

                        Select::make('client_id')
                            ->label(__('importanturl.form.client'))
                            ->relationship('client', 'pic_name')
                            ->getOptionLabelFromRecordUsing(function ($record) {
                                return ClientFormatter::formatClientDisplay($record->pic_name, $record->company_name);
                            })
                            ->preload()
                            ->searchable()
                            ->native(false)
                            ->dehydrated()
                            ->live()
                            ->prefixAction(
                                // Open the client in a new tab
                                Action::make('openClient')
                                    ->icon('heroicon-o-pencil-square')
                                    ->url(function (Get $get) {
                                        $clientId = $get('client_id');
                                        if (! $clientId) {
                                            return null;
                                        }

                                        return \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $clientId]);
                                    })
                                    ->openUrlInNewTab()
                                    ->visible(fn (Get $get) => (bool) $get('client_id'))
                            )
                            ->suffixAction(
                                Action::make('createClient')
                                    ->icon('heroicon-o-plus')
                                    ->url(\App\Filament\Resources\ClientResource::getUrl('create'))
                                    ->openUrlInNewTab()
                                    ->label(__('importanturl.form.create_client'))
                            )
                            ->nullable(),

                        Select::make('project_id')
                            ->label(__('importanturl.form.project'))
                            ->options(function () {
                                return \App\Models\Project::all()->mapWithKeys(function ($project) {
                                    $truncatedTitle = strlen($project->title) > 25
                                        ? substr($project->title, 0, 25).'...'
                                        : $project->title;

                                    return [$project->id => $truncatedTitle];
                                });
                            })
                            ->preload()
                            ->searchable()
                            ->native(false)
                            ->dehydrated()
                            ->live()
                            ->prefixAction(
                                // Open the project in a new tab
                                Action::make('openProject')
                                    ->icon('heroicon-o-pencil-square')
                                    ->url(function (Get $get) {
                                        $projectId = $get('project_id');
                                        if (! $projectId) {
                                            return null;
                                        }

                                        return \App\Filament\Resources\ProjectResource::getUrl('edit', ['record' => $projectId]);
                                    })
                                    ->openUrlInNewTab()
                                    ->visible(fn (Get $get) => (bool) $get('project_id'))
                            )
                            ->suffixAction(
                                Action::make('createProject')
                                    ->icon('heroicon-o-plus')
                                    ->url(\App\Filament\Resources\ProjectResource::getUrl('create'))
                                    ->openUrlInNewTab()
                                    ->label(__('importanturl.form.create_project'))
                            )
                            ->nullable(),
                    ]),

                    TextInput::make('url')
                        ->label(__('importanturl.form.important_url'))
                        ->helperText(__('importanturl.form.important_url_note'))
                        ->required()
                        ->hintAction(
                            fn (Get $get) => blank($get('url')) ? null : Action::make('openUrl')
                                ->icon('heroicon-m-arrow-top-right-on-square')
                                ->label(__('importanturl.form.open_url'))
                                ->url(fn () => $get('url'), true)
                                ->tooltip(__('importanturl.form.important_url_helper'))
                        )
                        ->url(),
                ]),

                Section::make()
                    ->heading(function (Get $get) {
                        $count = 0;

                        // Add count of extra_information items
                        $extraInfo = $get('extra_information') ?? [];
                        $count += count($extraInfo);

                        $title = __('importanturl.section.extra_info');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new \Illuminate\Support\HtmlString($title.' '.$badge);
                    })
                    ->collapsible(true)
                    ->live()
                    ->schema([
                        RichEditor::make('notes')
                            ->label(__('importanturl.form.notes'))
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
                            ->live()
                            // Character limit helper text
                            ->helperText(function (Get $get) {
                                $raw = $get('notes') ?? '';
                                if (empty($raw)) {
                                    return __('importanturl.form.notes_helper', ['count' => 500]);
                                }

                                // Optimized character counting - strip tags and count
                                $textOnly = strip_tags($raw);
                                $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                $remaining = max(0, 500 - mb_strlen($textOnly));

                                return __('importanturl.form.notes_helper', ['count' => $remaining]);
                            })
                            // Block save if over 500 visible characters
                            ->rule(function (Get $get): Closure {
                                return function (string $attribute, $value, Closure $fail) {
                                    if (empty($value)) {
                                        return;
                                    }
                                    $textOnly = strip_tags($value);
                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $textOnly = trim(preg_replace('/\s+/', ' ', $textOnly));
                                    if (mb_strlen($textOnly) > 500) {
                                        $fail(__('importanturl.form.notes_warning'));
                                    }
                                };
                            })
                            ->nullable(),

                        Repeater::make('extra_information')
                            ->label(__('importanturl.form.extra_information'))
                            // ->relationship('extra_information')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('importanturl.form.extra_title'))
                                            ->maxLength(100)
                                            ->debounce(1000)
                                            ->columnSpanFull(),
                                        RichEditor::make('value')
                                            ->label(__('importanturl.form.extra_value'))
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
                                            ->live()
                                            ->reactive()
                                            // Character limit reactive function
                                            ->helperText(function (Get $get) {
                                                $raw = $get('value') ?? '';
                                                if (empty($raw)) {
                                                    return __('importanturl.form.notes_helper', ['count' => 500]);
                                                }

                                                // Optimized character counting - strip tags and count
                                                $textOnly = strip_tags($raw);
                                                $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                $remaining = max(0, 500 - mb_strlen($textOnly));

                                                return __('importanturl.form.notes_helper', ['count' => $remaining]);
                                            })
                                            // Block save if over 500 visible characters
                                            ->rule(function (Get $get): Closure {
                                                return function (string $attribute, $value, Closure $fail) {
                                                    if (empty($value)) {
                                                        return;
                                                    }
                                                    $textOnly = strip_tags($value);
                                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $textOnly = trim(preg_replace('/\s+/', ' ', $textOnly));
                                                    if (mb_strlen($textOnly) > 500) {
                                                        $fail(__('importanturl.form.notes_warning'));
                                                    }
                                                };
                                            })
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->addActionLabel(__('importanturl.form.add_extra_info'))
                            ->addActionAlignment(Alignment::Start)
                            ->cloneable()
                            ->reorderable()
                            ->collapsible(true)
                            ->collapsed()
                            ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('importanturl.form.title_placeholder_short'))
                            ->live()
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Disable record URL and record action for all records
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([
                TextColumn::make('id')
                    ->label(__('importanturl.table.id'))
                    ->sortable(),
                Tables\Columns\ViewColumn::make('title')
                    ->label(__('importanturl.table.title'))
                    ->view('filament.resources.important-url-resource.title-column')
                    ->sortable(),

                Tables\Columns\ViewColumn::make('client_id')
                    ->label(__('importanturl.table.client'))
                    ->view('filament.resources.important-url-resource.client-column')
                    ->sortable(),

                Tables\Columns\ViewColumn::make('project_id')
                    ->label(__('importanturl.table.project'))
                    ->view('filament.resources.important-url-resource.project-column')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('importanturl.table.created_at'))
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('j/n/y, h:i A'))
                    ->sortable(),

                Tables\Columns\ViewColumn::make('updated_at')
                    ->label(__('importanturl.table.updated_at_by'))
                    ->view('filament.resources.important-url-resource.updated-by-column')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('client_id')
                    ->label(__('importanturl.filters.client_id'))
                    ->relationship('client', 'pic_name')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return ClientFormatter::formatClientDisplay($record->pic_name, $record->company_name);
                    })
                    ->preload()
                    ->searchable()
                    ->multiple(),
                SelectFilter::make('project_id')
                    ->label(__('importanturl.filters.project_id'))
                    ->relationship('project', 'title')
                    ->preload()
                    ->searchable()
                    ->multiple(),
                TrashedFilter::make()
                    ->label(__('importanturl.filter.trashed'))
                    ->searchable(), // To show trashed or only active
            ])
            ->actions([
                Tables\Actions\Action::make('open_url')
                    ->label('')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->tooltip(function ($record) {
                        $url = $record->url;

                        return strlen($url) > 50 ? substr($url, 0, 47).'...' : $url;
                    }),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->hidden(fn ($record) => $record->trashed()),

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
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            ImportantUrlActivityLogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImportantUrls::route('/'),
            'create' => Pages\CreateImportantUrl::route('/create'),
            'edit' => Pages\EditImportantUrl::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('importanturl.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('importanturl.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('importanturl.labels.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('importanturl.navigation_group'); // Grouping imporant url under Resources
    }

    public static function getNavigationSort(): ?int
    {
        return 44; // Adjust the navigation sort order as needed
    }
}
