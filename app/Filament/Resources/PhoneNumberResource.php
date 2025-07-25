<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PhoneNumberResource\Pages;
use App\Filament\Resources\PhoneNumberResource\RelationManagers;
use App\Models\PhoneNumber;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\PasswordInput;
use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PhoneNumberResource extends Resource
{
    protected static ?string $model = PhoneNumber::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->required()
                    ->tel(), // format for telephone input
                Textarea::make('notes')
                    ->maxLength(1000)
                    ->rows(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('title')->searchable()->sortable()->limit(10),
                TextColumn::make('phone')->searchable(),
                TextColumn::make('notes')->limit(30),
                TextColumn::make('created_at')->dateTime('d/m/y H:i')->sortable(),
            ])
            ->filters([
                TrashedFilter::make(), // To show trashed or only active
            ])
            ->actions([
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
        return 'Data Management';
    }
}
