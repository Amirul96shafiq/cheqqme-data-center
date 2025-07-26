<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientResource\RelationManagers;
use App\Models\Client;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Client Information')
                    ->schema([
                        TextInput::make('pic_name')->label('Person-in-charge Name')->required(),
                        TextInput::make('pic_email')->label('Person-in-charge Email')->email()->required(),
                        TextInput::make('pic_contact_number')->label('Person-in-charge Contact Number')->required()->tel(),
                    ])
                    ->columns(3),

                Section::make('Client\'s Company Information')
                    ->schema([
                        TextInput::make('company_name')->label('Company Name')->required(),
                        TextInput::make('company_email')->label('Company Email')->email()->nullable(),
                        Textarea::make('company_address')->label('Company Address')->rows(2)->nullable(),
                        Textarea::make('billing_address')->label('Billing Address')->rows(2)->nullable(),
                    ])
                    ->columns(2),
                
                Section::make('Client Extra Information')
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
                TextColumn::make('pic_name')->label('PIC Name')->searchable(),
                TextColumn::make('pic_email')->label('PIC Email')->searchable(),
                TextColumn::make('pic_contact_number')->label('PIC Contact Number')->searchable(),
                TextColumn::make('company_name')->label('Company')->searchable(),
                TextColumn::make('created_at')->dateTime('d/m/y H:i')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return 'Data Management'; // Grouping clients under Data Management
    }
    public static function getNavigationSort(): ?int
    {
        return 11; // Adjust the navigation sort order as needed
    }
}
