<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\{TextInput, Select, FileUpload, Radio, Textarea, Grid};
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Document Details')
                    ->schema([
                        TextInput::make('title')
                            ->label('Document Title')
                            ->required()
                            ->maxLength(50),

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
                Section::make('Document Extra Details')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->nullable()
                            ->maxLength(500),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('title')->label('Document')->sortable()->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('client.company_name')->label('Client')->sortable()->searchable(),
                TextColumn::make('project.title')->label('Project')->sortable()->searchable(),
                TextColumn::make('created_at')->dateTime('d/m/y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options([
                    'internal' => 'Internal',
                    'external' => 'External',
                ]),
                SelectFilter::make('client_id')->label('Client')->relationship('client', 'company_name'),
                SelectFilter::make('project_id')->label('Project')->relationship('project', 'title'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('View')
                    ->url(fn($record) => $record->type === 'external'
                        ? $record->url
                        : asset('storage/' . $record->file_path))
                    ->icon('heroicon-o-eye')
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
