<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhoneNumberResource\Pages;
use App\Filament\Resources\PhoneNumberResource\RelationManagers;
use App\Models\PhoneNumber;

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

class PhoneNumberResource extends Resource
{
    protected static ?string $model = PhoneNumber::class;

    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Phone Number Details')
                    ->schema([
                        TextInput::make('Phone Number title')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone Number')
                            ->required()
                            ->tel(),
                    ])
                    ->columns(2),

                Section::make('Phone Number Extra Details')
                    ->schema([
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
                TextColumn::make('title')->label('Title')->searchable()->sortable()->limit(10),
                TextColumn::make('phone')->label('Phone')->searchable(),
                TextColumn::make('created_at')->dateTime('d/m/Y, h:i A')->sortable(),
                TextColumn::make('updated_at')->dateTime('d/m/Y, h:i A') ->sortable(),
            ])
            ->filters([
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
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
            'index' => Pages\ListPhoneNumbers::route('/'),
            'create' => Pages\CreatePhoneNumber::route('/create'),
            'edit' => Pages\EditPhoneNumber::route('/{record}/edit'),
        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return 'Data Management'; // Grouping phone numbers under Data Management
    }
    public static function getNavigationSort(): ?int
    {
        return 55; // Adjust the navigation sort order as needed
    }
}
