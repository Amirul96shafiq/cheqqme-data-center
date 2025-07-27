<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportantUrlResource\Pages;
use App\Filament\Resources\ImportantUrlResource\RelationManagers;
use App\Models\ImportantUrl;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\{TextInput, Select, FileUpload, Radio, Textarea, Grid};
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

class ImportantUrlResource extends Resource
{
    protected static ?string $model = ImportantUrl::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Important URL Information')->schema([
                    Grid::make('3')->schema([
                        TextInput::make('title')->label('Important URL Title')->required()->maxLength(50),
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

                    TextInput::make('url')
                        ->label('Important URL')
                        ->required()
                        ->suffixAction(
                            Action::make('openUrl')
                                ->icon('heroicon-m-arrow-top-right-on-square')
                                ->url(fn($record) => $record?->url ?? '#', true) // true = open in new tab
                                ->tooltip('Open URL in new tab')
                                ->visible(fn($record) => filled($record?->url))
                        )
                        ->url(),
                ]),
                Section::make('Important URL Extra Information')->schema([
                    Textarea::make('notes')
                        ->maxLength(1000)
                        ->rows(4),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),

                TextColumn::make('title')->label('Title')->sortable()->searchable(),

                TextColumn::make('url')
                    ->label('Link')
                    ->url(fn($record) => $record->url, true)
                    ->openUrlInNewTab()
                    ->copyable()
                    ->limit(20),

                TextColumn::make('project.title')
                    ->label('Project')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('client.company_name')
                    ->label('Client')
                    ->sortable()
                    ->searchable(),

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
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('client_id')->label('Client')->relationship('client', 'company_name'),
                SelectFilter::make('project_id')->label('Project')->relationship('project', 'title'),
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
            'index' => Pages\ListImportantUrls::route('/'),
            'create' => Pages\CreateImportantUrl::route('/create'),
            'edit' => Pages\EditImportantUrl::route('/{record}/edit'),
        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return 'Data Management'; // Grouping imporant url under Data Management
    }
    public static function getNavigationSort(): ?int
    {
        return 44; // Adjust the navigation sort order as needed
    }
    public static function getModelLabel(): string
    {
        return 'Important URL';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Important URLs';
    }
    public static function getNavigationLabel(): string
    {
        return 'Important URLs';
    }
}
