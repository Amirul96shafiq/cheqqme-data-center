<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;

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

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Project Details')
                    ->schema([
                        TextInput::make('title')->label('Project Title')->required()->maxLength(50),

                        TextInput::make('project_url')->label('Project URL')->url()->nullable(),

                        Select::make('client_id')->label('Client')->relationship('client', 'company_name')->searchable()->preload()->nullable(),
                        Textarea::make('description')->label('Project Description')->rows(3)->nullable()->maxLength(200),
                        Select::make('status')->label('Project Status')->options(['Planning' => 'Planning','In Progress' => 'In Progress','Completed' => 'Completed',])->default('Planning')->required(),
                    ]),
                Section::make('Project Extra Details')
                    ->schema([
                        Textarea::make('notes')->label('Notes')->rows(3)->nullable()->maxLength(500),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('client.company_name')->label('Client')->sortable()->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'primary' => 'Planning',
                        'warning' => 'In Progress',
                        'success' => 'Completed',
                    ]),
                TextColumn::make('created_at')->dateTime('d/m/y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Planning' => 'Planning',
                        'In Progress' => 'In Progress',
                        'Completed' => 'Completed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return 'Data Management'; // Grouping projects under Data Management
    }
    public static function getNavigationSort(): ?int
    {
        return 22; // Adjust the navigation sort order as needed
    }
}
