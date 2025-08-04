<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Closure;
use Filament\Forms\Components\{TextInput, Select, FileUpload, Radio, Textarea, RichEditor, Grid};
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\PasswordInput;
use Filament\Forms\Components\Password;
use Illuminate\Support\Facades\Hash;
use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\{ViewAction, EditAction, DeleteAction, RestoreAction};
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('document.section.document_info'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('title')->label(__('document.form.document_title'))->required()->maxLength(50),

                            Select::make('project_id')
                                ->label(__('document.form.project'))
                                ->relationship('project', 'title')
                                ->preload()
                                ->searchable()
                                ->nullable(),

                            Select::make('client_id')
                                ->label(__('document.form.client'))
                                ->relationship('client', 'company_name')
                                ->preload()
                                ->searchable()
                                ->nullable(),
                        ]),

                        Radio::make('type')
                            ->label(__('document.form.document_type'))
                            ->options([
                                'external' => __('document.form.external'),
                                'internal' => __('document.form.internal'),
                            ])
                            ->required()
                            ->live(),

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
                Section::make(__('document.section.document_extra_info'))
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
                            //->maxLength(500)
                            ->extraAttributes([
                                'style' => 'resize: vertical;',
                            ])
                            ->reactive()
                            //Character limit reactive function
                            ->helperText(function (Get $get) {
                                $raw = $get('notes') ?? '';
                                // 1. Strip all HTML tags
                                $noHtml = strip_tags($raw);
                                // 2. Decode HTML entities (e.g., &nbsp; -> actual space)
                                $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                // 3. Count as-is — includes normal spaces, line breaks, etc.
                                $remaining = 500 - mb_strlen($decoded);
                                return __(
                                    __('document.form.notes_helper', ['count' => $remaining])
                                );
                            })
                            // Block save if over 500 visible characters
                            ->rule(function (Get $get): Closure {
                                return function (string $attribute, $value, Closure $fail) {
                                    $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                    if (mb_strlen($textOnly) > 500) {
                                        $fail(__('document.form.notes_warning'));
                                    }
                                };
                            })
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->url(fn($record) => route('filament.admin.resources.documents.edit', $record)),
                TextColumn::make('title')->label(__('document.table.title'))->sortable()->searchable()->limit(20),
                TextColumn::make('type')
                    ->label(__('document.table.type'))
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'internal' => __('document.table.internal'),
                        'external' => __('document.table.external'),
                    }),
                TextColumn::make('project.title')->label(__('document.table.project'))->sortable()->searchable()->limit(20),
                TextColumn::make('created_at')
                    ->label(__('document.table.created_at'))
                    ->dateTime('j/n/y, h:i A')->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('document.table.updated_at_by'))
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
                            $parts = explode(' ', $user->name);
                            $first = array_shift($parts);
                            $initials = implode(' ', array_map(fn($p) => mb_substr($p, 0, 1) . '.', $parts));
                            $formattedName = trim($first . ' ' . $initials);
                        }

                        return $state?->format('j/n/y, h:i A') . " ({$formattedName})";
                    })
                    ->sortable()
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make('type')->options([
                    'internal' => __('document.table.internal'),
                    'external' => __('document.table.external'),
                ]),
                SelectFilter::make('client_id')->label(__('document.table.client'))->relationship('client', 'company_name'),
                SelectFilter::make('project_id')->label(__('document.table.project'))->relationship('project', 'title'),
                TrashedFilter::make(), // To show trashed or only active
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ]);
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
