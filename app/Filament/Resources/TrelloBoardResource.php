<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrelloBoardResource\Pages;
use App\Filament\Resources\TrelloBoardResource\RelationManagers\TrelloBoardActivityLogRelationManager;
use App\Models\TrelloBoard;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
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
use Schmeits\FilamentCharacterCounter\Forms\Components\RichEditor;

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

                                            if (! empty($boardName)) {
                                                // Convert to title case and replace hyphens/underscores with spaces
                                                $formattedName = ucwords(str_replace(['-', '_'], ' ', $boardName));
                                                $set('name', $formattedName);
                                            }
                                        }
                                    })
                                    ->hintAction(
                                        fn (Get $get) => blank($get('url')) ? null : Action::make('openUrl')
                                            ->icon('heroicon-m-arrow-top-right-on-square')
                                            ->label(__('trelloboard.form.open_url'))
                                            ->url(fn () => $get('url'), true)
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

                        Forms\Components\Radio::make('status')
                            ->label(__('trelloboard.form.status'))
                            ->options([
                                'active' => __('trelloboard.form.status_active'),
                                'draft' => __('trelloboard.form.status_draft'),
                            ])
                            ->default('active')
                            ->inline()
                            ->required()
                            ->helperText(__('trelloboard.form.status_helper'))
                            ->disabled(function (Get $get) {
                                // Check if we're in edit mode by looking for record in route
                                $recordId = request()->route('record');
                                if ($recordId) {
                                    // We're editing - get the record from route
                                    $record = TrelloBoard::find($recordId);

                                    return $record && $record->created_by !== auth()->id();
                                }

                                // We're creating - never disable
                                return false;
                            })
                            ->visible(function (Get $get) {
                                // Check if we're in edit mode by looking for record in route
                                $recordId = request()->route('record');
                                if ($recordId) {
                                    // We're editing - get the record from route
                                    $record = TrelloBoard::find($recordId);

                                    return $record && $record->created_by === auth()->id();
                                }

                                // We're creating - always show
                                return true;
                            }),

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
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new \Illuminate\Support\HtmlString($title.' '.$badge);
                    })
                    ->collapsible(true)
                    ->collapsed()
                    ->live()
                    ->schema([

                        RichEditor::make('notes')
                            ->label(__('trelloboard.form.trelloboard_notes'))
                            ->maxLength(500)
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
                                            ->maxLength(500)
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
                            ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('trelloboard.form.title_placeholder_short'))
                            ->live(onBlur: true)
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
                    ->url(fn ($record) => route('filament.admin.resources.trello-boards.edit', $record->id))
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('trelloboard.table.board_name'))
                    ->searchable()
                    ->sortable()
                    ->limit(20),

                Tables\Columns\TextColumn::make('url')
                    ->label(__('trelloboard.table.board_url'))
                    ->searchable()
                    ->limit(40)
                    ->copyable()
                    ->color('primary')
                    ->url(fn ($record) => $record->url)
                    ->toggleable(),

                Tables\Columns\IconColumn::make('show_on_boards')
                    ->label(__('trelloboard.table.show_on_boards'))
                    ->boolean()
                    ->alignment(Alignment::Center),

                Tables\Columns\TextColumn::make('status')
                    ->label(__('trelloboard.table.status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'draft' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => __('trelloboard.table.status_active'),
                        'draft' => __('trelloboard.table.status_draft'),
                        default => $state,
                    })
                    ->toggleable()
                    ->visible(true)
                    ->alignment(Alignment::Center),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('trelloboard.table.created_at_by'))
                    ->since()
                    ->tooltip(function ($record) {
                        $createdAt = $record->created_at;

                        if (! $createdAt) {
                            return null;
                        }

                        $formatted = $createdAt->format('j/n/y, h:i A');

                        $creator = $record->createdBy ?? null;
                        $creatorName = $creator?->short_name ?? $creator?->name;

                        return $creatorName ? $formatted.' ('.$creatorName.')' : $formatted;
                    })
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\ViewColumn::make('updated_at')
                    ->label(__('trelloboard.table.updated_at_by'))
                    ->view('filament.resources.trello-board-resource.updated-by-column')
                    ->sortable(),

            ])
            ->modifyQueryUsing(function ($query) {
                return $query->visibleToUser();
            })
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

                SelectFilter::make('status')
                    ->label(__('trelloboard.table.status'))
                    ->options([
                        'active' => __('trelloboard.table.status_active'),
                        'draft' => __('trelloboard.table.status_draft'),
                    ])
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
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->tooltip(function ($record) {
                        $url = $record->url;

                        return strlen($url) > 50 ? substr($url, 0, 47).'...' : $url;
                    }),

                Tables\Actions\ViewAction::make()
                    ->label(__('trelloboard.actions.view')),

                Tables\Actions\EditAction::make()
                    ->label(__('trelloboard.actions.edit'))
                    ->hidden(fn ($record) => $record && $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('toggle_show_on_boards')
                        ->label(fn ($record) => $record->show_on_boards
                            ? __('trelloboard.actions.hide_from_boards')
                            : __('trelloboard.actions.show_on_boards'))
                        ->icon(fn ($record) => $record->show_on_boards ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn ($record) => $record->show_on_boards ? 'warning' : 'success')
                        ->action(function ($record) {
                            $record->update([
                                'show_on_boards' => ! $record->show_on_boards,
                                'updated_by' => auth()->id(),
                            ]);

                            // Show notification about sidebar refresh
                            \Filament\Notifications\Notification::make()
                                ->title(__('trelloboard.actions.status_updated'))
                                ->body(__('trelloboard.actions.refresh_sidebar_notification'))
                                ->success()
                                ->send();
                        })
                        ->tooltip(fn ($record) => $record->show_on_boards
                            ? __('trelloboard.actions.hide_from_boards')
                            : __('trelloboard.actions.show_on_boards'))
                        ->hidden(fn ($record) => $record->trashed()),

                    Tables\Actions\Action::make('toggle_status')
                        ->label(fn ($record) => $record->status === 'active'
                            ? __('trelloboard.actions.make_draft')
                            : __('trelloboard.actions.make_active'))
                        ->icon(fn ($record) => $record->status === 'active' ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn ($record) => $record->status === 'active' ? 'warning' : 'success')
                        ->action(function ($record) {
                            $newStatus = $record->status === 'active' ? 'draft' : 'active';

                            $record->update([
                                'status' => $newStatus,
                                'updated_by' => auth()->id(),
                            ]);

                            // Show success notification
                            \Filament\Notifications\Notification::make()
                                ->title(__('trelloboard.actions.status_updated'))
                                ->body($newStatus === 'active'
                                    ? __('trelloboard.actions.board_activated')
                                    : __('trelloboard.actions.board_made_draft'))
                                ->success()
                                ->send();
                        })
                        ->tooltip(fn ($record) => $record->status === 'active'
                            ? __('trelloboard.actions.make_draft_tooltip')
                            : __('trelloboard.actions.make_active_tooltip'))
                        ->hidden(fn ($record) => $record->trashed() || $record->created_by !== auth()->id()),

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

                    Tables\Actions\BulkAction::make('toggle_show_on_boards')
                        ->label(__('trelloboard.actions.toggle_show_on_boards'))
                        ->icon('heroicon-o-eye')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (! $record->trashed()) {
                                    $record->update([
                                        'show_on_boards' => ! $record->show_on_boards,
                                        'updated_by' => auth()->id(),
                                    ]);
                                }
                            }

                            // Show notification about sidebar refresh
                            \Filament\Notifications\Notification::make()
                                ->title(__('trelloboard.actions.status_updated'))
                                ->body(__('trelloboard.actions.refresh_sidebar_notification'))
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->modalHeading(__('trelloboard.actions.toggle_show_on_boards_modal_heading'))
                        ->modalDescription(__('trelloboard.actions.toggle_show_on_boards_modal_description'))
                        ->modalSubmitActionLabel(__('trelloboard.actions.toggle_show_on_boards_modal_confirm')),

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
