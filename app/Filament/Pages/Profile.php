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
                            ->placeholder(fn(callable $get) => $get('username')),

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
                                        if (!$state instanceof TemporaryUploadedFile) {
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
                                    ->maxSize(20480) // 20MB
                                    ->columnSpanFull()
                                    ->helperText(__('user.form.cover_image_helper'))
                                    ->afterStateUpdated(function ($state) {
                                        if (!$state instanceof TemporaryUploadedFile) {
                                            return;
                                        }

                                        // Delete old cover image if exists
                                        $oldCoverImage = auth()->user()->cover_image;
                                        if ($oldCoverImage && Storage::exists($oldCoverImage)) {
                                            Storage::delete($oldCoverImage);
                                        }
                                    })
                                    ->uploadingMessage(__('user.form.uploading'))
                                    ->uploadProgressIndicatorPosition('right')
                                    ->reorderable(false)
                                    ->appendFiles(false)
                                    ->openable(false)
                                    ->downloadable(false)
                                    ->deletable(true),
                            ])
                            ->columns(1),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columns(1),

                Forms\Components\Section::make(__('user.section.google_connection_settings'))
                    ->description(__('user.section.google_connection_settings_description'))
                    ->schema([
                        Forms\Components\Placeholder::make('google_connection')
                            ->label(__('user.form.google_connection'))
                            ->content(function () {
                                $user = auth()->user();
                                if ($user->hasGoogleAuth()) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<div class="flex items-center justify-between">
                                            <span class="text-sm text-gray-600 dark:text-gray-400">' . __('user.form.google_description') . '</span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                                Connected
                                            </span>
                                        </div>'
                                    );
                                }

                                return new \Illuminate\Support\HtmlString(
                                    '<div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Not connected to Google</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            Not Connected
                                        </span>
                                    </div>'
                                );
                            })
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->headerActions([
                        Forms\Components\Actions\Action::make('disconnect_google')
                            ->label(__('user.form.disconnect_google'))
                            ->color('danger')
                            ->outlined()
                            ->icon('heroicon-o-link-slash')
                            ->visible(fn() => auth()->user()->hasGoogleAuth())
                            ->requiresConfirmation()
                            ->modalHeading(__('user.form.disconnect_google_confirm'))
                            ->modalDescription(__('user.form.disconnect_google_description'))
                            ->modalSubmitActionLabel(__('user.form.disconnect'))
                            ->modalCancelActionLabel(__('user.form.cancel'))
                            ->modalWidth('md')
                            ->action('confirmDisconnectGoogle'),

                        Forms\Components\Actions\Action::make('connect_google')
                            ->label(__('user.form.connect_google'))
                            ->color('success')
                            ->outlined()
                            ->icon('heroicon-o-link')
                            ->visible(fn() => !auth()->user()->hasGoogleAuth())
                            ->requiresConfirmation()
                            ->modalHeading(__('user.form.connect_google'))
                            ->modalDescription(__('user.form.google_description'))
                            ->modalSubmitActionLabel(__('user.form.connect_google'))
                            ->modalCancelActionLabel(__('user.form.cancel'))
                            ->modalWidth('md')
                            ->action('connectGoogle'),
                    ])
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
                                            if ($value && !Hash::check($value, auth()->user()->password)) {
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
                                    ->dehydrated(fn($state) => filled($state))
                                    ->same('password_confirmation')
                                    ->minLength(5)
                                    ->columnSpanFull(),
                                // CONFIRM password
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label(__('user.form.confirm_password'))
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(fn($state) => filled($state))
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

    /**
     * Confirm disconnect Google account
     */
    public function confirmDisconnectGoogle(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $user->disconnectGoogle();

        Notification::make()
            ->title(__('user.form.google_disconnected'))
            ->body(__('user.form.google_disconnected_body'))
            ->success()
            ->send();
    }

    /**
     * Connect Google account
     */
    public function connectGoogle()
    {
        // Redirect to Google OAuth
        return redirect()->route('auth.google');
    }
}
