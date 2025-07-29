<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
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
                Section::make('Document Information')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('title')->label('Document Title')->required()->maxLength(50),

                            Select::make('project_id')
                                ->label('Project')
                                ->relationship('project', 'title')
                                ->preload()
                                ->searchable()
                                ->nullable(),

                            Select::make('client_id')
                                ->label('Client')
                                ->relationship('client', 'company_name')
                                ->preload()
                                ->searchable()
                                ->nullable(),
                        ]),

                        Radio::make('type')
                            ->label('Document Type')
                            ->options([
                                'external' => 'External',
                                'internal' => 'Internal',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('url')
                            ->label('Document URL')
                            ->helperText('URL for external documents')
                            ->visible(fn(Get $get) => $get('type') === 'external')
                            ->suffixAction(
                                Action::make('openUrl')
                                    ->icon('heroicon-m-arrow-top-right-on-square')
                                    ->url(fn($livewire) => $livewire->data['url'] ?? '#', true)
                                    ->tooltip('Open URL in new tab')
                                    ->disabled(
                                        fn($livewire) =>
                                        str($livewire::class)->contains('Create') ||
                                        blank($livewire->data['url'] ?? null)
                                    )
                            )
                            ->url()
                            ->nullable(),


                        FileUpload::make('file_path')
                            ->label('Upload Document')
                            ->helperText('Upload internal documents here (PDF, JPEG, PNG, DOC, DOCX, XLS, XLSX, PPT, PPTX)')
                            ->visible(fn(Get $get) => $get('type') === 'internal')
                            ->directory('documents')
                            ->disk('public') // Or 'local' if you’re not using storage:link
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
                Section::make('Document Extra Information')
                    ->schema([
                        RichEditor::make('notes')
                            ->label('Notes')
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
                            ->maxLength(500)
                            ->extraAttributes([
                                'style' => 'resize: vertical;',
                            ])
                            ->reactive()
                            //Character limit reactive function
                            ->helperText(function (Get $get) {
                                $raw = $get('notes') ?? '';
                                $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($raw))); // Strip HTML + normalize whitespace
                                $remaining = 500 - mb_strlen($textOnly); // Use mb_strlen for multibyte safety
                                return "{$remaining} characters remaining";
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
                TextColumn::make('title')->label('Title')->sortable()->searchable()->limit(20),
                TextColumn::make('type')->badge(),
                TextColumn::make('project.title')->label('Project')->sortable()->searchable()->limit(20),
                TextColumn::make('created_at')->dateTime('j/n/y, h:i A')->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated at (by)')
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
                    'internal' => 'Internal',
                    'external' => 'External',
                ]),
                SelectFilter::make('client_id')->label('Client')->relationship('client', 'company_name'),
                SelectFilter::make('project_id')->label('Project')->relationship('project', 'title'),
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

    public static function getRelations(): array
    {
        return [
            //
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

    public static function getNavigationGroup(): ?string
    {
        return 'Data Management'; // Grouping documents under Data Management
    }
    public static function getNavigationSort(): ?int
    {
        return 33; // Adjust the navigation sort order as needed
    }
}
