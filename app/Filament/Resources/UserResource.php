<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\UserActivityLogRelationManager;
use App\Helpers\TimezoneHelper;
use App\Models\User;
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
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Unique;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make(heading: __('user.section.user_info'))
                    ->schema([

                        Grid::make(3)
                            ->schema([

                                TextInput::make('username')
                                    ->label(__('user.form.username'))
                                    ->required()
                                    ->maxLength(100)
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
                                    ->placeholder(fn (callable $get) => $get('username'))
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
                                        modifyRuleUsing: fn (Unique $rule) => $rule->whereNull('deleted_at')
                                    ),

                                Hidden::make('Updated_by')->default(fn () => auth()->id())->dehydrated(),

                                Hidden::make('online_status')->default('invisible')->dehydrated(),

                            ]),

                    ]),

                Section::make(heading: __('user.section.password_info'))
                    ->description(fn (string $context) => $context === 'edit' ? __('user.section.password_info_description') : null)
                    ->schema([

                        Grid::make(3)
                            ->schema([

                                // Only show "Change password?" during editing
                                Toggle::make('change_password_toggle')
                                    ->label(__('user.form.change_password'))
                                    ->live()
                                    ->afterStateUpdated(function (bool $state, callable $set) {
                                        if (! $state) {
                                            $set('old_password', null);
                                            $set('password', null);
                                            $set('password_confirmation', null);
                                        }
                                    })
                                    ->visible(fn (string $context) => $context === 'edit'),

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
                                            fn (Get $get, string $context) => $context === 'create' || $get('change_password_toggle')
                                        ),
                                ]),

                            ]),

                        Grid::make(3)
                            ->schema([

                                // OLD PASSWORD
                                Forms\Components\TextInput::make('old_password')
                                    ->label(label: __('user.form.old_password'))
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(false)
                                    ->visible(
                                        fn (Get $get, string $context) => $context === 'edit' && $get('change_password_toggle') === true
                                    )
                                    ->rule(function (Get $get) {
                                        return function (string $attribute, $value, $fail) use ($get) {
                                            $record = $get('record');
                                            if ($record && $value && ! Hash::check($value, $record->password)) {
                                                $fail('The old password is incorrect.');
                                            }
                                        };
                                    }),

                                // NEW PASSWORD
                                TextInput::make('password')
                                    ->label(fn (string $context) => $context === 'edit' ? __('user.form.new_password') : __('user.form.new_password'))
                                    ->helperText(__('user.form.password_helper'))
                                    ->password()
                                    ->revealable()
                                    ->minLength(5)
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context) => $context === 'create')
                                    ->visible(
                                        fn (Get $get, string $context) => $context === 'create' || $get('change_password_toggle')
                                    )
                                    ->same('password_confirmation'),

                                // CONFIRM NEW PASSWORD
                                TextInput::make('password_confirmation')
                                    ->label(label: __('user.form.confirm_new_password'))
                                    ->password()
                                    ->revealable()
                                    ->required(
                                        fn (Get $get, string $context) => $context === 'create' || filled($get('password'))
                                    )
                                    ->visible(
                                        fn (Get $get, string $context) => $context === 'create' || $get('change_password_toggle')
                                    ),

                            ]),

                    ]),

                // Account deletion
                Section::make(heading: __('user.section.danger_zone'))
                    ->description(__('user.section.danger_zone_description'))
                    ->visible(fn (string $context) => $context === 'edit') // hide entire section when creating
                    ->Schema([

                        // Only show "User Deletion?" during editing
                        Toggle::make('user_delete')
                            ->label(label: __('user.form.user_deletion'))
                            ->onColor('danger')
                            ->offColor('gray')
                            ->live()
                            ->visible(fn (string $context) => $context === 'edit')
                            ->afterStateUpdated(function (bool $state, callable $set) {
                                if (! $state) {
                                    $set('delete_confirmation', null);
                                }
                            }),

                        // Delete confirmation as a second defense mechanism
                        TextInput::make('delete_confirmation')
                            ->label(__('user.form.user_confirm_title'))
                            ->placeholder(__('user.form.user_confirm_placeholder'))
                            ->helperText(__('user.form.user_confirm_helpertext'))
                            ->visible(
                                fn (Get $get, string $context) => $context === 'edit' && $get('user_delete') === true
                            )
                            ->live()
                            ->dehydrated(false),

                        Actions::make([
                            Action::make('deleteRecord')
                                ->label(__('user.actions.delete'))
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->visible(fn (Get $get) => $get('user_delete') === true)
                                ->disabled(fn (Get $get) => $get('delete_confirmation') !== 'CONFIRM DELETE USER')
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
                    ->badge()
                    ->color('primary')
                    ->extraAttributes(function ($record) {
                        $coverImageUrl = $record->getFilamentCoverImageUrl();
                        if ($coverImageUrl) {
                            return [
                                'data-cover-image-url' => $coverImageUrl,
                                'loading' => 'lazy', // Lazy load cover images
                                'fetchpriority' => 'low', // Low priority for decorative images
                            ];
                        }

                        return [];
                    }),

                ViewColumn::make('avatar')
                    ->label(__('user.table.avatar'))
                    ->view('filament.resources.user-resource.avatar-column')
                    ->alignCenter()
                    ->extraCellAttributes(function ($record) {
                        // Add loading="lazy" attribute to avatar column for image lazy loading
                        $coverImageUrl = $record->getFilamentCoverImageUrl();
                        if ($coverImageUrl) {
                            return [
                                'loading' => 'lazy',
                                'decoding' => 'async',
                            ];
                        }

                        return [
                            'loading' => 'lazy',
                            'decoding' => 'async',
                        ];
                    }),

                TextColumn::make('username')
                    ->label(__('user.table.username'))
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\ViewColumn::make('email')
                    ->label(__('user.table.email'))
                    ->view('filament.resources.user-resource.email-column')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('country_from_phone')
                    ->label(__('user.table.country'))
                    ->getStateUsing(function ($record) {
                        $phone = $record->phone ?? '';
                        $digits = preg_replace('/\D+/', '', $phone);

                        // Extract country code and determine country
                        if (str_starts_with($digits, '60')) {
                            return 'MY';
                        } elseif (str_starts_with($digits, '65')) {
                            return 'SG';
                        } elseif (str_starts_with($digits, '62')) {
                            return 'ID';
                        } elseif (str_starts_with($digits, '63')) {
                            return 'PH';
                        } elseif (str_starts_with($digits, '1')) {
                            return 'US';
                        }

                        return 'Unknown';
                    })
                    ->badge()
                    ->color('primary')
                    ->alignCenter()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('phone')
                    ->label(__('user.table.phone_number'))
                    ->searchable()
                    ->getStateUsing(function ($record) {
                        $phone = $record->phone;

                        return filled($phone) ? $phone : '-';
                    })
                    ->limit(20)
                    ->toggleable(),

                TextColumn::make('timezone')
                    ->label(__('user.table.timezone'))
                    ->getStateUsing(function ($record) {
                        return $record->timezone ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('user.table.created_at_by'))
                    ->since()
                    ->tooltip(function ($record) {
                        $createdAt = $record->created_at;

                        if (! $createdAt) {
                            return null;
                        }

                        $formatted = $createdAt->format('j/n/y, h:i A');

                        $updaterName = null;

                        if (method_exists($record, 'updatedBy')) {
                            $updater = $record->updatedBy;
                            $updaterName = $updater?->short_name ?? $updater?->name;
                        }

                        return $updaterName ? $formatted.' ('.$updaterName.')' : $formatted;
                    })
                    ->sortable()
                    ->toggleable(),

                // TextColumn::make('updated_at')
                //     ->label(__('user.table.updated_at_by'))
                //     ->formatStateUsing(function ($state, $record) {
                //         // Show '-' if there's no update or updated_by
                //         $updatedAt = $record->updated_at;
                //         $createdAt = $record->created_at;
                //         if (! $record->updated_by || ($updatedAt && $createdAt && $updatedAt->eq($createdAt))) {
                //             return '-';
                //         }

                //         $user = $record->updatedBy;
                //         $formattedName = 'Unknown';

                //         if ($user) {
                //             $formattedName = $user->short_name;
                //         }

                //         return $state?->format('j/n/y, h:i A')." ({$formattedName})";
                //     })
                //     ->sortable(),

            ])
            ->recordClasses(function ($record) {
                $coverImageUrl = $record->getFilamentCoverImageUrl();
                $classes = ['fi-table-row', 'user-table-row-border'];

                if ($coverImageUrl) {
                    $classes[] = 'cover-image-row';
                }

                return implode(' ', $classes);
            })
            ->modifyQueryUsing(function ($query) {
                return $query->with('updatedBy'); // Eager load the updatedBy relationship
            })
            ->filters([
                Filter::make('has_cover_image')
                    ->label(__('user.filter.has_cover_image'))
                    ->query(fn (Builder $query) => $query->whereNotNull('cover_image')),

                Filter::make('country_code')
                    ->label(__('user.filter.country_code'))
                    ->form([
                        \Filament\Forms\Components\Select::make('country_code')
                            ->label(__('user.filter.country_code'))
                            ->multiple()
                            ->options(function () {
                                // Get distinct country codes from existing phone numbers
                                $phoneNumbers = User::whereNotNull('phone')
                                    ->where('phone', '!=', '')
                                    ->pluck('phone')
                                    ->unique();

                                $countryCodes = [];
                                $countryMapping = [
                                    '60' => 'Malaysia',
                                    '62' => 'Indonesia',
                                    '65' => 'Singapore',
                                    '63' => 'Philippines',
                                    '1' => 'United States',
                                ];

                                foreach ($phoneNumbers as $phone) {
                                    // Extract digits only (handles both +60 and 60 formats)
                                    $digitsOnly = preg_replace('/\D+/', '', $phone);

                                    if (empty($digitsOnly)) {
                                        continue;
                                    }

                                    // Try different length country codes
                                    $firstTwo = substr($digitsOnly, 0, 2);
                                    $firstOne = substr($digitsOnly, 0, 1);

                                    if (isset($countryMapping[$firstTwo])) {
                                        $countryCode = $firstTwo;
                                        $countryName = $countryMapping[$firstTwo];
                                    } elseif (isset($countryMapping[$firstOne])) {
                                        $countryCode = $firstOne;
                                        $countryName = $countryMapping[$firstOne];
                                    } else {
                                        continue;
                                    }

                                    if (! isset($countryCodes[$countryCode])) {
                                        $countryCodes[$countryCode] = "{$countryName} (+{$countryCode})";
                                    }
                                }

                                // Sort by country code
                                ksort($countryCodes);

                                return $countryCodes;
                            }),
                    ])
                    ->query(function ($query, array $data) {
                        if (empty($data['country_code'])) {
                            return $query;
                        }

                        $selectedCodes = is_array($data['country_code']) ? $data['country_code'] : [$data['country_code']];

                        return $query->where(function ($q) use ($selectedCodes) {
                            foreach ($selectedCodes as $index => $code) {
                                $condition = function ($subQuery) use ($code) {
                                    $subQuery->where('phone', 'like', "+{$code}%")
                                        ->orWhere('phone', 'like', "{$code}%");
                                };

                                if ($index === 0) {
                                    $q->where($condition);
                                } else {
                                    $q->orWhere($condition);
                                }
                            }
                        });
                    }),

                SelectFilter::make('timezone')
                    ->label(__('user.filter.timezone'))
                    ->options(TimezoneHelper::getFlattenedTimezoneOptions())
                    ->multiple()
                    ->searchable(),

                TrashedFilter::make()
                    ->label(__('user.filter.trashed'))
                    ->searchable(), // To show trashed or only active
            ])
            ->actions([

                TableAction::make('personalize')
                    ->label(__('user.table.personalize'))
                    ->icon('heroicon-o-sparkles')
                    ->url(
                        fn (User $record) =>
                        // Only show for logged-in user's own account
                        auth()->id() === $record->id
                        ? filament()->getProfileUrl()
                        : '#'
                    )
                    ->openUrlInNewTab(false)
                    ->visible(
                        fn (User $record) =>
                        // Only visible to the logged-in user and only for their own account
                        auth()->id() === $record->id
                    ),

                Tables\Actions\EditAction::make()->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([

                    TableAction::make('settings')
                        ->label(__('user.table.settings'))
                        ->icon('heroicon-o-cog-6-tooth')
                        ->url(
                            fn (User $record) =>
                            // Only show for logged-in user's own account
                            auth()->id() === $record->id
                            ? \App\Filament\Pages\Settings::getUrl()
                            : '#'
                        )
                        ->openUrlInNewTab(false)
                        ->visible(
                            fn (User $record) =>
                            // Only visible to the logged-in user and only for their own account
                            auth()->id() === $record->id
                        ),

                    TableAction::make('chatbot-history')
                        ->label(__('user.table.chatbot-history'))
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->url(
                            fn (User $record) =>
                            // Only show for logged-in user's own account
                            auth()->id() === $record->id
                            ? \App\Filament\Pages\ChatbotHistory::getUrl()
                            : '#'
                        )
                        ->openUrlInNewTab(false)
                        ->visible(
                            fn (User $record) =>
                            // Only visible to the logged-in user and only for their own account
                            auth()->id() === $record->id
                        ),

                    ActivityLogTimelineTableAction::make('Log'),

                    Tables\Actions\RestoreAction::make(),

                    Tables\Actions\ForceDeleteAction::make(),

                ]),
            ])
            ->bulkActions([
                //
            ])
            // ->defaultSort('updated_at', 'desc')
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
        return __('user.navigation_group'); // Grouping imporant url under Resources
    }

    public static function getNavigationSort(): ?int
    {
        return 11; // Adjust the navigation sort order as needed
    }
}
