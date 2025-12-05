<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportantUrlResource\Pages;
use App\Filament\Resources\ImportantUrlResource\RelationManagers\ImportantUrlActivityLogRelationManager;
use App\Helpers\ClientFormatter;
use App\Models\ImportantUrl;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Schmeits\FilamentCharacterCounter\Forms\Components\RichEditor;

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
                // Section Controls Section
                Section::make(__('importanturl.form.section_controls'))
                    ->schema([
                        Grid::make(6)
                            ->schema([
                                // Additional Information Toggle
                                Toggle::make('enable_additional_information')
                                    ->label(__('importanturl.form.enable_additional_information'))
                                    ->default(function (?ImportantUrl $record) {
                                        // Enable if record has notes or extra_information
                                        if ($record) {
                                            $hasNotes = ! empty($record->notes);
                                            $hasExtraInfo = ! empty($record->extra_information) && is_array($record->extra_information);

                                            return $hasNotes || $hasExtraInfo;
                                        }

                                        return false;
                                    })
                                    ->live()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function (Set $set, $state, ?ImportantUrl $record) {
                                        // Double-check additional information on hydration and enable toggle if needed
                                        if ($record) {
                                            $hasNotes = ! empty($record->notes);
                                            $hasExtraInfo = ! empty($record->extra_information) && is_array($record->extra_information);

                                            if ($hasNotes || $hasExtraInfo) {
                                                $set('enable_additional_information', true);
                                            }
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        // When toggle is disabled, clear all additional information
                                        if (! $state) {
                                            $set('notes', null);
                                            $set('extra_information', []);
                                        }
                                    }),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('importanturl.section.important_url_info'))->schema([
                    Grid::make([
                        'default' => 1,
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                        '2xl' => 3,
                    ])->schema([

                        TextInput::make('title')
                            ->label(__('importanturl.form.important_url_title'))
                            ->required()
                            ->maxLength(100),

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
                    ->heading(__('importanturl.section.extra_info'))
                    ->visible(fn (Get $get) => $get('enable_additional_information'))
                    ->collapsible(true)
                    ->collapsed(false)
                    ->live()
                    ->schema([

                        RichEditor::make('notes')
                            ->label(__('importanturl.form.notes'))
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
                            ->addActionLabel(__('importanturl.form.add_extra_info'))
                            ->addActionAlignment(Alignment::Start)
                            ->cloneable()
                            ->reorderable()
                            ->collapsible(true)
                            ->collapsed()
                            ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('importanturl.form.title_placeholder_short'))
                            ->live(onBlur: true)
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),

                    ])
                    ->collapsible(),

                Section::make(__('importanturl.section.visibility_status'))
                    ->schema(function (Get $get) {
                        // Check if we're in edit mode by looking for record in route
                        $recordId = request()->route('record');
                        $isEditMode = $recordId !== null;
                        $canEditVisibility = true;

                        if ($isEditMode) {
                            // We're editing - get the record from route
                            $record = ImportantUrl::find($recordId);
                            $canEditVisibility = $record && $record->created_by === auth()->id();
                        }

                        if ($canEditVisibility) {
                            // User can edit visibility - show radio field
                            return [
                                \Filament\Forms\Components\Radio::make('visibility_status')
                                    ->label(__('importanturl.form.visibility_status'))
                                    ->options([
                                        'active' => __('importanturl.form.visibility_status_active'),
                                        'draft' => __('importanturl.form.visibility_status_draft'),
                                    ])
                                    ->default('active')
                                    ->inline()
                                    ->required()
                                    ->helperText(__('importanturl.form.visibility_status_helper')),
                            ];
                        } else {
                            // User cannot edit visibility - show message with clickable creator name
                            $creator = null;
                            if ($isEditMode && $record) {
                                $creator = $record->createdBy;
                            }

                            return [
                                \Filament\Forms\Components\Placeholder::make('visibility_status_readonly')
                                    ->label(__('importanturl.form.visibility_status'))
                                    ->content(new \Illuminate\Support\HtmlString(
                                        __('importanturl.form.visibility_status_helper_readonly').' '.
                                        \Blade::render('<x-clickable-creator-name :user="$user" />', ['user' => $creator]).
                                        '.'
                                    )),
                            ];
                        }
                    }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Disable record URL and record action for all records
            ->recordUrl(null)
            ->recordAction(null)
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['project', 'client', 'createdBy', 'updatedBy'])->visibleToUser())
            ->columns([

                TextColumn::make('id')
                    ->label(__('importanturl.table.id'))
                    ->url(fn ($record) => $record->trashed() ? null : route('filament.admin.resources.important-urls.edit', $record->id))
                    ->sortable(),

                Tables\Columns\ViewColumn::make('title')
                    ->label(__('importanturl.table.title'))
                    ->view('filament.resources.important-url-resource.title-column')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ViewColumn::make('client_id')
                    ->label(__('importanturl.table.client'))
                    ->view('filament.resources.important-url-resource.client-column')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\ViewColumn::make('project_id')
                    ->label(__('importanturl.table.project'))
                    ->view('filament.resources.important-url-resource.project-column')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('url')
                    ->label(__('importanturl.table.important_url'))
                    ->state(function ($record) {
                        return $record->url ?: '-';
                    })
                    ->copyable()
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->url ?: '';
                    })
                    ->toggleable()
                    ->searchable(),

                TextColumn::make('visibility_status')
                    ->label(__('importanturl.table.visibility_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => __('importanturl.table.visibility_status_active'),
                        'draft' => __('importanturl.table.visibility_status_draft'),
                        default => $state,
                    })
                    ->toggleable()
                    ->visible(true)
                    ->alignment(Alignment::Center),

                TextColumn::make('created_at')
                    ->label(__('importanturl.table.created_at_by'))
                    ->since()
                    ->tooltip(function ($record) {
                        $createdAt = $record->created_at;

                        if (! $createdAt) {
                            return null;
                        }

                        $formatted = $createdAt->format('j/n/y, h:i A');

                        $creatorName = null;

                        if (method_exists($record, 'createdBy')) {
                            $creator = $record->createdBy;
                            $creatorName = $creator?->short_name ?? $creator?->name;
                        }

                        return $creatorName ? $formatted.' ('.$creatorName.')' : $formatted;
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                Tables\Filters\SelectFilter::make('visibility_status')
                    ->label(__('importanturl.table.visibility_status'))
                    ->options([
                        'active' => __('importanturl.table.visibility_status_active'),
                        'draft' => __('importanturl.table.visibility_status_draft'),
                    ])
                    ->preload()
                    ->searchable(),

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

                Tables\Actions\ViewAction::make()
                    ->slideOver(),

                Tables\Actions\EditAction::make()
                    ->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('toggle_visibility_status')
                        ->label(fn ($record) => $record->visibility_status === 'active'
                            ? __('importanturl.actions.make_draft')
                            : __('importanturl.actions.make_active'))
                        ->icon(fn ($record) => $record->visibility_status === 'active' ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn ($record) => $record->visibility_status === 'active' ? 'warning' : 'success')
                        ->action(function ($record) {
                            $newStatus = $record->visibility_status === 'active' ? 'draft' : 'active';
                            $record->update([
                                'visibility_status' => $newStatus,
                                'updated_by' => auth()->id(),
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title(__('importanturl.actions.visibility_status_updated'))
                                ->body($newStatus === 'active'
                                    ? __('importanturl.actions.important_url_activated')
                                    : __('importanturl.actions.important_url_made_draft'))
                                ->success()
                                ->send();
                        })
                        ->tooltip(fn ($record) => $record->visibility_status === 'active'
                            ? __('importanturl.actions.make_draft_tooltip')
                            : __('importanturl.actions.make_active_tooltip'))
                        ->hidden(fn ($record) => $record->trashed() || $record->created_by !== auth()->id()),

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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Important URL Information Section (matches first section in form)
                Infolists\Components\Section::make(__('importanturl.section.important_url_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label(__('importanturl.form.important_url_title'))
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('client.company_name')
                            ->label(__('importanturl.form.client'))
                            ->placeholder(__('No client assigned')),

                        Infolists\Components\TextEntry::make('project.title')
                            ->label(__('importanturl.form.project'))
                            ->placeholder(__('No project assigned')),

                        Infolists\Components\TextEntry::make('url')
                            ->label(__('importanturl.form.important_url'))
                            ->copyable()
                            ->url(fn ($record) => $record->url)
                            ->openUrlInNewTab()
                            ->placeholder(__('No URL'))
                            ->columnSpanFull(),
                    ]),

                // Additional Information Section (matches second section in form)
                Infolists\Components\Section::make()
                    ->heading(function ($record) {
                        $count = count($record->extra_information ?? []);

                        $title = __('importanturl.section.extra_info');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new \Illuminate\Support\HtmlString($title.' '.$badge);
                    })
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label(__('importanturl.form.notes'))
                            ->markdown()
                            ->placeholder(__('No notes'))
                            ->columnSpanFull(),

                        Infolists\Components\RepeatableEntry::make('extra_information')
                            ->label(__('importanturl.form.extra_information'))
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label(__('importanturl.form.extra_title')),
                                Infolists\Components\TextEntry::make('value')
                                    ->label(__('importanturl.form.extra_value'))
                                    ->markdown(),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),

                // Visibility Status Information Section (matches third section in form)
                Infolists\Components\Section::make(__('importanturl.section.visibility_status'))
                    ->schema([
                        Infolists\Components\TextEntry::make('visibility_status')
                            ->label(__('importanturl.form.visibility_status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'draft' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => __('importanturl.form.visibility_status_active'),
                                'draft' => __('importanturl.form.visibility_status_draft'),
                                default => $state,
                            }),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('createdBy.name')
                                    ->label(__('Created by'))
                                    ->placeholder(__('Unknown')),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label(__('Created at'))
                                    ->dateTime('j/n/y, h:i A'),
                            ]),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('updatedBy.name')
                                    ->label(__('Updated by'))
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->dateTime('j/n/y, h:i A'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
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
