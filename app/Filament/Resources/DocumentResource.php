<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers\DocumentActivityLogRelationManager;
use App\Models\Document;
use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
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

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $recordTitleAttribute = 'title'; // Use 'title' as the record title attribute

    public static function getGloballySearchableAttributes(): array // This method defines which attributes are searchable globally
    {
        return ['title', 'project.title'];
    }

    public static function getGlobalSearchResultDetails($record): array // This method defines the details shown in global search results
    {
        return [
            __('document.search.project') => optional($record->project)->title,
            __('document.search.type') => ucfirst($record->type),
            __('document.search.url') => $record->url ?? '-',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('document.section.document_info'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('title')
                                    ->label(__('document.form.document_title'))
                                    ->required()
                                    ->maxLength(50),

                                Select::make('project_id')
                                    ->label(__('document.form.project'))
                                    ->relationship('project', 'title')
                                    ->preload()
                                    ->searchable()
                                    ->nullable(),

                                Select::make('type')
                                    ->label(__('document.form.document_type'))
                                    ->options([
                                        'external' => __('document.form.external'),
                                        'internal' => __('document.form.internal'),
                                    ])
                                    ->searchable()
                                    ->required()
                                    ->live(),
                            ]),

                        TextInput::make('url')
                            ->label(__('document.form.document_url'))
                            ->helperText(__('document.form.document_url_note'))
                            ->visible(fn(Get $get) => $get('type') === 'external')
                            ->hintAction(
                                fn(Get $get) => blank($get('url')) ? null : Action::make('openUrl')
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->label(__('document.form.open_url'))
                                    ->url(fn() => $get('url'), true)
                                    ->tooltip(__('document.form.document_url_helper'))
                                    ->visible(fn(Get $get) => !blank($get('url')) && filter_var($get('url'), FILTER_VALIDATE_URL))
                            )
                            ->url()
                            ->nullable(),

                        FileUpload::make('file_path')
                            ->label(__('document.form.document_upload'))
                            ->helperText(__('document.form.document_upload_helper'))
                            ->visible(fn(Get $get) => $get('type') === 'internal')
                            ->directory('documents')
                            ->disk('public')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->enableDownload()
                            ->enableOpen()
                            ->deletable()
                            ->acceptedFileTypes([
                                'application/pdf',
                                'image/jpeg',
                                'image/png',
                                'application/msword', // doc
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // docx
                                'application/vnd.ms-excel', // xls
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // xlsx
                                'application/vnd.ms-powerpoint', // ppt
                                'application/vnd.openxmlformats-officedocument.presentationml.presentation', // pptx
                            ])
                            ->maxFiles(10240)
                            ->nullable(),
                    ]),

                Section::make()
                    ->heading(function (Get $get) {
                        $count = 0;

                        // Add 1 if notes field is not empty
                        $notes = $get('notes');
                        if (!blank($notes) && trim(strip_tags($notes))) {
                            $count++;
                        }

                        // Add count of extra_information items
                        $extraInfo = $get('extra_information') ?? [];
                        $count += count($extraInfo);

                        $title = __('document.section.extra_info');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">(' . $count . ')</span>';

                        return new \Illuminate\Support\HtmlString($title . ' ' . $badge);
                    })
                    ->collapsible(true)
                    ->live()
                    ->schema([
                        RichEditor::make('notes')
                            ->label(__('document.form.notes'))
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
                            ->live(onBlur: true)
                            // Character limit helper text - only updates on blur to prevent focus loss
                            ->helperText(function (Get $get) {
                                $raw = $get('notes') ?? '';
                                if (empty($raw)) {
                                    return __('document.form.notes_helper', ['count' => 500]);
                                }

                                // Optimized character counting - strip tags and count
                                $textOnly = strip_tags($raw);
                                $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                $remaining = max(0, 500 - mb_strlen($textOnly));

                                return __('document.form.notes_helper', ['count' => $remaining]);
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
                                        $fail(__('document.form.notes_warning'));
                                    }
                                };
                            })
                            ->nullable(),

                        Repeater::make('extra_information')
                            ->label(__('document.form.extra_information'))
                            // ->relationship('extra_information')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('document.form.extra_title'))
                                            ->maxLength(100)
                                            ->debounce(1000)
                                            ->columnSpanFull(),
                                        RichEditor::make('value')
                                            ->label(__('document.form.extra_value'))
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
                                            ->debounce(300)
                                            ->reactive()
                                            // Character limit reactive function
                                            ->helperText(function (Get $get) {
                                                $raw = $get('value') ?? '';
                                                if (empty($raw)) {
                                                    return __('document.form.notes_helper', ['count' => 500]);
                                                }

                                                // Optimized character counting - strip tags and count
                                                $textOnly = strip_tags($raw);
                                                $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                $remaining = max(0, 500 - mb_strlen($textOnly));

                                                return __('document.form.notes_helper', ['count' => $remaining]);
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
                                                        $fail(__('document.form.notes_warning'));
                                                    }
                                                };
                                            })
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->addActionLabel(__('document.form.add_extra_info'))
                            ->addActionAlignment(Alignment::Start)
                            ->cloneable()
                            ->reorderable()
                            ->collapsible(true)
                            ->collapsed()
                            ->itemLabel(fn(array $state): string => !empty($state['title']) ? $state['title'] : __('document.form.title_placeholder_short'))
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
            // Disable record URL for trashed records
            ->recordUrl(fn($record) => $record->trashed() ? null : static::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('document.table.title'))
                    ->sortable()
                    ->searchable()
                    ->limit(20)
                    ->url(function ($record) {
                        if ($record->type === 'external' && $record->url) {
                            return $record->url;
                        }

                        return $record->file_path ? asset('storage/' . ltrim($record->file_path, '/')) : null;
                    })
                    ->openUrlInNewTab()
                    ->tooltip(function ($record) {
                        if ($record->type === 'external' && $record->url) {
                            $url = $record->url;

                            return strlen($url) > 50 ? substr($url, 0, 47) . '...' : $url;
                        }
                    }),
                TextColumn::make('type')
                    ->label(__('document.table.type'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'internal' => __('document.table.internal'),
                        'external' => __('document.table.external'),
                        default => ucfirst($state),
                    }),
                TextColumn::make('project.title')
                    ->label(__('document.table.project'))->sortable()->searchable()->limit(20),
                TextColumn::make('created_at')
                    ->label(__('document.table.created_at'))
                    ->dateTime('j/n/y, h:i A')->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('document.table.updated_at_by'))
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
                SelectFilter::make('project_id')
                    ->label(__('document.table.project'))
                    ->relationship('project', 'title')
                    ->preload()
                    ->searchable()
                    ->multiple(),
                SelectFilter::make('type')
                    ->label(__('document.table.type'))
                    ->options([
                        'internal' => __('document.table.internal'),
                        'external' => __('document.table.external'),
                    ])
                    ->multiple()
                    ->preload()
                    ->searchable(),
                TrashedFilter::make()
                    ->searchable(),
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
            DocumentActivityLogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('document.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('document.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('document.labels.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('document.navigation_group'); // Grouping documents under Data Management
    }

    public static function getNavigationSort(): ?int
    {
        return 33; // Adjust the navigation sort order as needed
    }
}
