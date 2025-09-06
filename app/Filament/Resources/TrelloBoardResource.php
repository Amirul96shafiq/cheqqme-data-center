<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrelloBoardResource\Pages;
use App\Filament\Resources\TrelloBoardResource\RelationManagers\TrelloBoardActivityLogRelationManager;
use App\Models\TrelloBoard;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

class TrelloBoardResource extends Resource
{
    protected static ?string $model = TrelloBoard::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'url'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('trelloboard.search.board_url') => $record->url,
            __('trelloboard.search.show_on_board') => $record->show_on_boards ? __('trelloboard.search.show_on_board_true') : __('trelloboard.search.show_on_board_false'),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('trelloboard.section.trello_board_info'))
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->label(__('trelloboard.form.board_url'))
                                    ->placeholder('https://trello.com/b/12345678/board-name')
                                    ->helperText(__('trelloboard.form.board_url_note'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, ?string $state) {
                                        if (empty($state)) {
                                            return;
                                        }

                                        // Extract board name from URL path
                                        $parsedUrl = parse_url($state);
                                        if (isset($parsedUrl['path'])) {
                                            $path = trim($parsedUrl['path'], '/');
                                            $pathParts = explode('/', $path);

                                            // Get the last part of the path (board name)
                                            $boardName = end($pathParts);

                                            if (!empty($boardName)) {
                                                // Convert to title case and replace hyphens/underscores with spaces
                                                $formattedName = ucwords(str_replace(['-', '_'], ' ', $boardName));
                                                $set('name', $formattedName);
                                            }
                                        }
                                    })
                                    ->hintAction(
                                        fn(Get $get) => blank($get('url')) ? null : Action::make('openUrl')
                                            ->icon('heroicon-m-arrow-top-right-on-square')
                                            ->label(__('trelloboard.form.open_url'))
                                            ->url(fn() => $get('url'), true)
                                            ->tooltip(__('trelloboard.form.board_url_helper'))
                                    )
                                    ->url(),

                                Forms\Components\TextInput::make('name')
                                    ->label(__('trelloboard.form.board_name'))
                                    ->required()
                                    ->placeholder('Board Name')
                                    ->helperText(__('trelloboard.form.board_name_helper')),
                            ]),
                    ]),

                Section::make(__('trelloboard.section.display_info'))
                    ->schema([
                        Toggle::make('show_on_boards')
                            ->label(__('trelloboard.form.show_on_boards'))
                            ->default(true),
                    ]),

                Section::make()
                    ->heading(function (Get $get) {
                        $count = 0;

                        // Add count of extra_information items
                        $extraInfo = $get('extra_information') ?? [];
                        $count += count($extraInfo);

                        $title = __('trelloboard.section.extra_info');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">(' . $count . ')</span>';

                        return new \Illuminate\Support\HtmlString($title . ' ' . $badge);
                    })
                    ->collapsible(true)
                    ->live()
                    ->schema([
                        RichEditor::make('notes')
                            ->label(__('trelloboard.form.trelloboard_notes'))
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
                                    return __('trelloboard.form.notes_helper', ['count' => 500]);
                                }

                                // Optimized character counting - strip tags and count
                                $textOnly = strip_tags($raw);
                                $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                $remaining = max(0, 500 - mb_strlen($textOnly));

                                return __('trelloboard.form.notes_helper', ['count' => $remaining]);
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
                                        $fail(__('trelloboard.form.notes_warning'));
                                    }
                                };
                            })
                            ->nullable(),

                        Repeater::make('extra_information')
                            ->label(__('trelloboard.form.extra_information'))
                            // ->relationship('extra_information')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('trelloboard.form.extra_title'))
                                            ->maxLength(100)
                                            ->debounce(1000)
                                            ->columnSpanFull(),
                                        RichEditor::make('value')
                                            ->label(__('trelloboard.form.extra_value'))
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
                                                    return __('trelloboard.form.notes_helper', ['count' => 500]);
                                                }

                                                // Optimized character counting - strip tags and count
                                                $textOnly = strip_tags($raw);
                                                $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                $remaining = max(0, 500 - mb_strlen($textOnly));

                                                return __('trelloboard.form.notes_helper', ['count' => $remaining]);
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
                                                        $fail(__('trelloboard.form.notes_warning'));
                                                    }
                                                };
                                            })
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->addActionLabel(__('trelloboard.form.add_extra_info'))
                            ->addActionAlignment(Alignment::Start)
                            ->cloneable()
                            ->reorderable()
                            ->collapsible(true)
                            ->collapsed()
                            ->itemLabel(fn(array $state): string => !empty($state['title']) ? $state['title'] : __('trelloboard.form.title_placeholder_short'))
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
                    ->label(__('trelloboard.table.id'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('trelloboard.table.board_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('url')
                    ->label(__('trelloboard.table.board_url'))
                    ->searchable()
                    ->sortable()
                    ->limit(20)
                    ->copyable(),

                Tables\Columns\IconColumn::make('show_on_boards')
                    ->label(__('trelloboard.table.show_on_boards'))
                    ->boolean()
                    ->alignment(Alignment::Center),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('trelloboard.table.created_at'))
                    ->dateTime('j/n/y, h:i A')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(__('trelloboard.table.updated_at_by'))
                    ->formatStateUsing(function ($state, $record) {
                        // Show '-' if there's no update or updated_by
                        $updatedAt = $record->updated_at;
                        $createdAt = $record->created_at;
                        if (!$record->updated_by || ($updatedAt && $createdAt && $updatedAt->eq($createdAt))) {
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
                SelectFilter::make('show_on_boards')
                    ->label(__('trelloboard.table.show_on_boards'))
                    ->options([
                        true => __('trelloboard.table.show_on_boards_true'),
                        false => __('trelloboard.table.show_on_boards_false'),
                    ])
                    ->multiple()
                    ->preload()
                    ->searchable(),

                TrashedFilter::make()
                    ->label(__('trelloboard.filter.trashed'))
                    ->searchable(), // To show trashed or only active
            ])
            ->actions([
                Tables\Actions\Action::make('open_url')
                    ->label('')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->url(fn($record) => $record->url)
                    ->openUrlInNewTab()
                    ->tooltip(function ($record) {
                        $url = $record->url;

                        return strlen($url) > 50 ? substr($url, 0, 47) . '...' : $url;
                    }),
                Tables\Actions\ViewAction::make()
                    ->label(__('trelloboard.actions.view')),
                Tables\Actions\EditAction::make()
                    ->label(__('trelloboard.actions.edit'))
                    ->hidden(fn($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    ActivityLogTimelineTableAction::make(__('trelloboard.actions.log')),
                    Tables\Actions\DeleteAction::make()
                        ->label(__('trelloboard.actions.delete')),
                    Tables\Actions\RestoreAction::make()
                        ->label(__('trelloboard.actions.restore')),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label(__('trelloboard.actions.force_delete')),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label(__('trelloboard.actions.delete')),
                    Tables\Actions\RestoreBulkAction::make()
                        ->label(__('trelloboard.actions.restore')),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->label(__('trelloboard.actions.force_delete')),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            TrelloBoardActivityLogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrelloBoards::route('/'),
            'create' => Pages\CreateTrelloBoard::route('/create'),
            'edit' => Pages\EditTrelloBoard::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('trelloboard.navigation_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('trelloboard.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('trelloboard.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('trelloboard.labels.plural');
    }

    public static function getNavigationSort(): ?int
    {
        return 10;
    }
}
