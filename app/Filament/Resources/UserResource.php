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
use Illuminate\Validation\Rules\Unique;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make(heading: __('user.section.user_info'))
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('username')
                                ->label(__('user.form.username'))
                                ->required()
                                ->maxLength(20)
                                ->unique(ignoreRecord: true)
                                ->reactive()
                                ->debounce(500) // Delay the reaction so user can finish typing
                                ->extraAttributes([
                                    'x-on:blur' => "
                                        if (\$refs.username && !\$refs.username.value) {
                                            \$refs.username.value = \$el.value;
                                            \$el.dispatchEvent(new Event('input')); // Force model update
                                            \$refs.username.dispatchEvent(new Event('input'));
                                        }
                                    ",
                                ])
                                ->extraAlpineAttributes(['x-ref' => 'username']),

                            TextInput::make('name')
                                ->label(__('user.form.name'))
                                ->nullable()
                                ->extraAlpineAttributes(['x-ref' => 'name'])
                                ->helperText(__('user.form.name_helper'))
                                ->placeholder(fn(callable $get) => $get('username'))
                                ->maxLength(50),

                            TextInput::make('email')
                                ->label(__('user.form.email'))
                                ->required()
                                ->email()
                                ->maxLength(60)
                                ->unique(
                                    table: 'users',
                                    column: 'email',
                                    ignoreRecord: true,
                                    modifyRuleUsing: fn(Unique $rule) => $rule->whereNull('deleted_at')
                                ),

                            Hidden::make('Updated_by')->default(fn() => auth()->id())->dehydrated(),
                        ])
                    ]),

                Section::make(heading: __('user.section.password_info'))
                    ->description(fn(string $context) => $context === 'edit' ? __('user.section.password_info_description') : null)
                    ->schema([

                        // Only show "Change password?" during editing
                        Toggle::make('change_password')
                            ->label(__('user.form.change_password'))
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
                                ->label(__('user.form.generate_password'))
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
                                ->label(label: __('user.form.old_password'))
                                ->password()
                                ->revealable()
                                ->dehydrated(false)
                                ->visible(
                                    fn(Get $get, string $context) =>
                                    $context === 'edit' && $get('change_password') === true
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
                                ->label(fn(string $context) => $context === 'edit' ? __('user.form.new_password') : __('user.form.new_password'))
                                ->helperText(__('user.form.password_helper'))
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
                                ->label(label: __('user.form.confirm_new_password'))
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
                Section::make(heading: __('user.section.danger_zone'))
                    ->description(__('user.section.danger_zone_description'))
                    ->visible(fn(string $context) => $context === 'edit') // hide entire section when creating
                    ->Schema([
                        // Only show "User Deletion?" during editing
                        Toggle::make('user_delete')
                            ->label(label: __('user.form.user_deletion'))
                            ->onColor('danger')
                            ->offColor('gray')
                            ->live()
                            ->visible(fn(string $context) => $context === 'edit')
                            ->afterStateUpdated(function (bool $state, callable $set) {
                                if (!$state) {
                                    $set('delete_confirmation', null);
                                }
                            }),

                        // Delete confirmation as a second defense mechanism
                        TextInput::make('delete_confirmation')
                            ->label(__('user.form.user_confirm_title'))
                            ->placeholder(__('user.form.user_confirm_placeholder'))
                            ->helperText(__('user.form.user_confirm_helpertext'))
                            ->visible(
                                fn(Get $get, string $context) =>
                                $context === 'edit' && $get('user_delete') === true
                            )
                            ->live()
                            ->dehydrated(false),

                        Actions::make([
                            Action::make('deleteRecord')
                                ->label(__('user.actions.delete'))
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->visible(fn(Get $get) => $get('user_delete') === true)
                                ->disabled(fn(Get $get) => $get('delete_confirmation') !== 'CONFIRM DELETE USER')
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
            // Disable record URL for trashed records
            ->recordUrl(fn($record) => $record->trashed() ? null : static::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')->label(__('user.table.id')),
                TextColumn::make('username')->label(__('user.table.username'))->searchable()->sortable()->limit(20),
                TextColumn::make('name')->label(__('user.table.name'))->searchable()->sortable()->limit(20),
                TextColumn::make('email')->label(__('user.table.email'))->searchable()->sortable()->limit(50),
                TextColumn::make('created_at')->label(__('user.table.created_at'))->dateTime('j/n/y, h:i A')->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('user.table.updated_at_by'))
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
                            $formattedName = $user->short_name;
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
                Tables\Actions\EditAction::make()->hidden(fn($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    ActivityLogTimelineTableAction::make('Log'),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ])
            ])
            ->bulkActions([
                /*Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),*/
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ActivitylogRelationManager::class,
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
    public static function getNavigationLabel(): string
    {
        return __('user.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('user.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('user.labels.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('user.navigation_group'); // Grouping imporant url under Data Management
    }
    public static function getNavigationSort(): ?int
    {
        return 11; // Adjust the navigation sort order as needed
    }
}
