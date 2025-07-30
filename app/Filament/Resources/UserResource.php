<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;

use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\{TextInput, Toggle, Grid, Group, Hidden};
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\PasswordInput;
use Filament\Forms\Components\Password;
use Illuminate\Support\Facades\Hash;
use Filament\Resources\Resource;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('User Information')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('username')->label('Username')->required()->maxLength(20),
                            TextInput::make('name')->label('Name')->nullable()->maxLength(50),
                            TextInput::make('email')->label('Email')->required()->email()->maxLength(60),
                            Hidden::make('Updated_by')->default(fn() => auth()->id())->dehydrated(),
                        ])
                    ]),

                Section::make('Password Information')
                    ->description('Enable Change Password? toggle to view this field')
                    ->schema([
                        // Only show "Change password?" during editing
                        Toggle::make('change_password')
                            ->label('Change Password?')
                            ->live()
                            ->afterStateUpdated(function (bool $state, callable $set) {
                                if (!$state) {
                                    $set('old_password', null);
                                    $set('password', null);
                                    $set('password_confirmation', null);
                                }
                            })
                            ->visible(fn(string $context) => $context === 'edit'),

                        // Generate password feature
                        Forms\Components\Actions::make([
                            Action::make('generatePassword')
                                ->label('Generate Strong Password')
                                ->icon('heroicon-o-code-bracket-square')
                                ->color('gray')
                                ->action(function ($set) {
                                    $generated = str()->random(16);
                                    $set('password', $generated);
                                })
                                ->visible(
                                    fn(Get $get, string $context) =>
                                    $context === 'create' || $get('change_password')
                                ),
                        ]),

                        Grid::make(3)->schema([

                            // OLD PASSWORD
                            Forms\Components\TextInput::make('old_password')
                                ->label('Old Password')
                                ->password()
                                ->revealable()
                                ->dehydrated(false)
                                ->visible(
                                    fn(Get $get, string $context) =>
                                    $context === 'create' || $get('change_password')
                                )
                                ->rule(function () {
                                    return function (string $attribute, $value, $fail) {
                                        if ($value && !Hash::check($value, auth()->user()->password)) {
                                            $fail('The old password is incorrect.');
                                        }
                                    };
                                }),

                            // NEW PASSWORD
                            TextInput::make('password')
                                ->label(fn(string $context) => $context === 'edit' ? 'New Password' : 'Password')
                                ->password()
                                ->revealable()
                                ->minLength(5)
                                ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                                ->dehydrated(fn($state) => filled($state))
                                ->required(fn(string $context) => $context === 'create')
                                ->visible(
                                    fn(Get $get, string $context) =>
                                    $context === 'create' || $get('change_password')
                                )
                                ->same('password_confirmation'),

                            // CONFIRM NEW PASSWORD
                            TextInput::make('password_confirmation')
                                ->label('Confirm New Password')
                                ->password()
                                ->revealable()
                                ->required(
                                    fn(Get $get, string $context) =>
                                    $context === 'create' || filled($get('password'))
                                )
                                ->visible(
                                    fn(Get $get, string $context) =>
                                    $context === 'create' || $get('change_password')
                                ),
                        ]),
                    ]),

                // Account deletion
                Section::make('Danger Zone')
                ->description('Enable User Deletion? toggle to view this field')
                    ->Schema([
                        // Only show "Change password?" during editing
                        Toggle::make('user_delete')
                            ->label('User Deletion?')
                            ->live()
                            ->visible(fn(string $context) => $context === 'edit'),
                        // Delete button
                        Actions::make([
                            Action::make('deleteRecord')
                                ->label('Delete User')
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->visible(
                                    fn(Get $get, string $context) =>
                                    $context === 'create' || $get('user_delete')
                                )
                                ->action(function ($record, $livewire) {
                                    $record->delete();

                                    // Optionally redirect after delete
                                    $livewire->redirect('/admin/users');
                                }),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->limit(20),
                TextColumn::make('email')->searchable()->sortable()->limit(50),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return 'User Management'; // Grouping users under User Management
    }
    public static function getNavigationSort(): ?int
    {
        return 11; // Adjust the navigation sort order as needed
    }
}
