<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\UserActivityLogRelationManager;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Password;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Support\Facades\FilamentColor;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Unique;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Spatie\Color\Rgb;

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
                        ]),
                    ]),

                Section::make(heading: __('user.section.password_info'))
                    ->description(fn(string $context) => $context === 'edit' ? __('user.section.password_info_description') : null)
                    ->schema([

                        Grid::make(3)->schema([
                            // Only show "Change password?" during editing
                            Toggle::make('change_password_toggle')
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
                                        fn(Get $get, string $context) => $context === 'create' || $get('change_password_toggle')
                                    ),
                            ]),
                        ]),

                        Grid::make(3)->schema([
                            // OLD PASSWORD
                            Forms\Components\TextInput::make('old_password')
                                ->label(label: __('user.form.old_password'))
                                ->password()
                                ->revealable()
                                ->dehydrated(false)
                                ->visible(
                                    fn(Get $get, string $context) => $context === 'edit' && $get('change_password_toggle') === true
                                )
                                ->rule(function (Get $get) {
                                    return function (string $attribute, $value, $fail) use ($get) {
                                        $record = $get('record');
                                        if ($record && $value && !Hash::check($value, $record->password)) {
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
                                    fn(Get $get, string $context) => $context === 'create' || $get('change_password_toggle')
                                )
                                ->same('password_confirmation'),

                            // CONFIRM NEW PASSWORD
                            TextInput::make('password_confirmation')
                                ->label(label: __('user.form.confirm_new_password'))
                                ->password()
                                ->revealable()
                                ->required(
                                    fn(Get $get, string $context) => $context === 'create' || filled($get('password'))
                                )
                                ->visible(
                                    fn(Get $get, string $context) => $context === 'create' || $get('change_password_toggle')
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
                                fn(Get $get, string $context) => $context === 'edit' && $get('user_delete') === true
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
            // Disable record URL for all records
            ->recordUrl(null)
            ->columns([
                TextColumn::make('id')
                    ->label(__('user.table.id'))
                    ->sortable()
                    ->extraAttributes(function ($record) {
                        $coverImageUrl = $record->getFilamentCoverImageUrl();
                        if ($coverImageUrl) {
                            return [
                                'data-cover-image-url' => $coverImageUrl,
                            ];
                        }

                        return [];
                    }),

                ImageColumn::make('avatar')
                    ->label(__('user.table.avatar'))
                    ->circular()
                    ->defaultImageUrl(function ($record) {
                        $name = str(Filament::getNameForDefaultAvatar($record))
                            ->trim()
                            ->explode(' ')
                            ->map(fn(string $segment): string => filled($segment) ? mb_substr($segment, 0, 1) : '')
                            ->join(' ');

                        $backgroundColor = Rgb::fromString('rgb(' . FilamentColor::getColors()['gray'][950] . ')')->toHex();

                        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&color=FFFFFF&background=' . str($backgroundColor)->after('#');
                    })
                    ->size(50)
                    ->width(50)
                    ->height(50)
                    ->extraImgAttributes(function ($record) {
                        $coverImageUrl = $record->getFilamentCoverImageUrl();
                        if ($coverImageUrl) {
                            return ['class' => 'border-4 border-white/50'];
                        }

                        return ['class' => 'border-0'];
                    })
                    ->alignCenter(),

                TextColumn::make('username')
                    ->label(__('user.table.username'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('email')
                    ->label(__('user.table.email'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('created_at')
                    ->label(__('user.table.created_at'))
                    ->dateTime('j/n/y, h:i A')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label(__('user.table.updated_at_by'))
                    ->formatStateUsing(function ($state, $record) {
                        // Show '-' if there's no update or updated_by
                        $updatedAt = $record->updated_at;
                        $createdAt = $record->created_at;
                        if (!$record->updated_by || ($updatedAt && $createdAt && $updatedAt->eq($createdAt))) {
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
            ->recordClasses(function ($record) {
                $coverImageUrl = $record->getFilamentCoverImageUrl();
                $classes = ['fi-table-row'];

                if ($coverImageUrl) {
                    $classes[] = 'cover-image-row';
                }

                return implode(' ', $classes);
            })
            ->modifyQueryUsing(function ($query) {
                return $query->with('updatedBy'); // Eager load the updatedBy relationship
            })
            ->filters([
                TrashedFilter::make(), // To show trashed or only active
            ])
            ->actions([
                TableAction::make('personalize')
                    ->label('Personalize')
                    ->icon('heroicon-o-paint-brush')
                    ->url(
                        fn(User $record) =>
                        // Only show for logged-in user's own account
                        auth()->id() === $record->id
                        ? filament()->getProfileUrl()
                        : '#'
                    )
                    ->openUrlInNewTab(false)
                    ->visible(
                        fn(User $record) =>
                        // Only visible to the logged-in user and only for their own account
                        auth()->id() === $record->id
                    ),

                Tables\Actions\EditAction::make()->hidden(fn($record) => $record->trashed()),
                
                Tables\Actions\ActionGroup::make([
                    ActivityLogTimelineTableAction::make('Log'),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                //
            ])
            ->defaultSort('updated_at', 'desc')
            ->defaultPaginationPageOption(5);
    }

    public static function getRelations(): array
    {
        return [
            UserActivityLogRelationManager::class,
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
