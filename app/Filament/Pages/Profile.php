<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class Profile extends EditProfile
{
    protected static string $view = 'filament.pages.profile';

    public string $old_password = ''; // For old password input

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('user.section.profile_settings'))
                    ->description(__('user.section.profile_settings_description'))
                    ->schema([
                        Forms\Components\TextInput::make('username')
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

                        $this->getNameFormComponent()
                            ->nullable()
                            ->extraAlpineAttributes(['x-ref' => 'name'])
                            ->helperText(__('user.form.name_helper'))
                            ->placeholder(fn (callable $get) => $get('username')),

                        $this->getEmailFormComponent()->label(__('user.form.email')),

                        Forms\Components\Fieldset::make(__('user.form.personalize'))
                            ->schema([
                                Forms\Components\FileUpload::make('avatar')
                                    ->label(__('user.form.avatar'))
                                    ->image()
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->directory('avatars')
                                    ->moveFiles()
                                    ->columnSpanFull()
                                    ->avatar()
                                    ->afterStateUpdated(function ($state) {
                                        if (! $state instanceof TemporaryUploadedFile) {
                                            return;
                                        }

                                        // Delete old avatar if exists
                                        $oldAvatar = auth()->user()->avatar;
                                        if ($oldAvatar && Storage::exists($oldAvatar)) {
                                            Storage::delete($oldAvatar);
                                        }
                                    }),

                                Forms\Components\FileUpload::make('cover_image')
                                    ->label(__('user.form.cover_image'))
                                    ->image()
                                    ->imageEditor()
                                    ->directory('covers')
                                    ->moveFiles()
                                    ->preserveFilenames()
                                    ->imageResizeMode('cover')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120) // 5MB
                                    ->columnSpanFull()
                                    ->helperText(__('user.form.cover_image_helper'))
                                    ->afterStateUpdated(function ($state) {
                                        if (! $state instanceof TemporaryUploadedFile) {
                                            return;
                                        }

                                        // Delete old cover image if exists
                                        $oldCoverImage = auth()->user()->cover_image;
                                        if ($oldCoverImage && Storage::exists($oldCoverImage)) {
                                            Storage::delete($oldCoverImage);
                                        }
                                    }),
                            ])
                            ->columns(1),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columns(1),

                Forms\Components\Section::make(__('user.section.password_settings'))
                    ->description(__('user.section.password_info_description_profile'))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        // OLD password
                        Forms\Components\Fieldset::make(__('user.form.old_password'))
                            ->columns(1)
                            ->schema([
                                Forms\Components\TextInput::make('old_password')
                                    ->label(__('user.form.password'))
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(false)
                                    ->requiredWith(['password', 'password_confirmation'])
                                    ->rule(function () {
                                        return function (string $attribute, $value, $fail) {
                                            if ($value && ! Hash::check($value, auth()->user()->password)) {
                                                $fail('The old password is incorrect.');
                                            }
                                        };
                                    })
                                    ->columnSpanFull(),
                            ]),
                        // NEW password
                        Forms\Components\Fieldset::make(__('user.form.new_password'))
                            ->columns(1)
                            ->schema([
                                // Generate password feature
                                Forms\Components\Actions::make([
                                    Action::make('generatePassword')
                                        ->label(__('user.form.generate_password'))
                                        ->icon('heroicon-o-code-bracket-square')
                                        ->color('gray')
                                        ->action(function ($set) {
                                            $generated = str()->random(16);
                                            $set('password', $generated);
                                        }),
                                ]),
                                Forms\Components\TextInput::make('password')
                                    ->label(__('user.form.password'))
                                    ->password()
                                    ->helperText(__('user.form.password_helper'))
                                    ->revealable()
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->same('password_confirmation')
                                    ->minLength(5)
                                    ->columnSpanFull(),
                                // CONFIRM password
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label(__('user.form.confirm_password'))
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public function afterSave(): void
    {
        // If user changed the password, log them out
        if (filled($this->form->getState()['password'] ?? null)) {
            Notification::make()
                ->title(__('user.form.saved_password'))
                ->body(__('user.form.saved_password_body'))
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title(__('user.form.saved'))
                ->body(__('user.form.saved_body'))
                ->success()
                ->send();
        }
    }

    // Disabled Default notification in Profile
    protected function getSavedNotification(): ?Notification
    {
        return null;
    }

    // Override to prevent the notifications form component error
    protected function getNotificationsFormComponent(): ?Forms\Components\Component
    {
        return null;
    }
}
