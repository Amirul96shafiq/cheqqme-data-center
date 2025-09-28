<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\EditProfile;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class Profile extends EditProfile
{
    protected static string $view = 'filament.pages.profile';

    public string $old_password = ''; // For old password input

    public function mount(): void
    {
        parent::mount();

        // Check if user was redirected here after successful OAuth connection
        $this->js('
            if (sessionStorage.getItem("google_connection_success") === "true") {
                sessionStorage.removeItem("google_connection_success");
                $wire.call("showGoogleConnectionSuccess");
            }
            
        ');
    }

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

                        \App\Forms\Components\OnlineStatusSelect::make('online_status')
                            ->label(__('user.form.online_status'))
                            ->default(\App\Services\OnlineStatus\StatusManager::getDefaultStatus())
                            ->helperText(__('user.form.online_status_helper')),

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
                                    ->itemPanelAspectRatio('0.25')
                                    ->imageResizeMode('cover')
                                    ->imageCropAspectRatio('4:1')
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(20480) // 20MB
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
                                    })
                                    ->uploadingMessage(__('user.form.uploading'))
                                    ->uploadProgressIndicatorPosition('right')
                                    ->reorderable(false)
                                    ->appendFiles(false)
                                    ->openable(false)
                                    ->downloadable(false)
                                    ->deletable(true),

                                // Web app background enabled
                                Forms\Components\Toggle::make('web_app_background_enabled')
                                    ->label(__('user.form.web_app_background'))
                                    ->helperText(__('user.form.web_app_background_helper'))
                                    ->default(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->dispatch('background-preview-updated', enabled: $state);
                                    }),

                                // Background preview component
                                Forms\Components\Placeholder::make('background_preview')
                                    ->label(__('user.form.background_preview'))
                                    ->content(function () {
                                        // Get enabled state
                                        $enabled = $this->form->getState()['web_app_background_enabled'] ?? false;
                                        $enabledText = __('user.form.enabled');
                                        $disabledText = __('user.form.disabled');

                                        // Determine initial theme - default to light, but JavaScript will override immediately
                                        $initialTheme = 'light';
                                        $initialSrc = '/images/stylized-bg-'.($enabled ? 'enabled' : 'disabled').'-sample-'.$initialTheme.'.png';

                                        return new \Illuminate\Support\HtmlString('
                                            <div 
                                                x-data="{
                                                    enabled: '.($enabled ? 'true' : 'false').',
                                                    enabledText: \''.$enabledText.'\',
                                                    disabledText: \''.$disabledText.'\',
                                                    init() {
                                                        // Set up watchers
                                                        this.$watch(\'enabled\', () => this.updatePreview());
                                                        
                                                        // Listen for background preview updates
                                                        this.$el.addEventListener(\'background-preview-updated\', (e) => {
                                                            this.enabled = e.detail.enabled;
                                                            this.updatePreview();
                                                        });
                                                        
                                                        // Listen for theme changes
                                                        this.$el.addEventListener(\'theme-changed\', (e) => {
                                                            this.updatePreview();
                                                        });
                                                        
                                                        // Update preview after a short delay to ensure DOM is ready
                                                        setTimeout(() => this.updatePreview(), 10);
                                                    },
                                                    updatePreview() {
                                                        const imgLight = this.$refs.previewImg;
                                                        const imgDark = this.$refs.previewImgDark;
                                                        
                                                        if (imgLight && imgDark) {
                                                            const enabledSuffix = this.enabled ? \'enabled\' : \'disabled\';
                                                            const lightSrc = \'/images/stylized-bg-\' + enabledSuffix + \'-sample-light.png\';
                                                            const darkSrc = \'/images/stylized-bg-\' + enabledSuffix + \'-sample-dark.png\';
                                                            
                                                            imgLight.src = lightSrc;
                                                            imgDark.src = darkSrc;
                                                            
                                                            // Force text update by manually updating the span element
                                                            const textSpan = this.$el.querySelector(\'[x-text]\');
                                                            if (textSpan) {
                                                                const newText = this.enabled ? this.enabledText : this.disabledText;
                                                                textSpan.textContent = newText;
                                                            }
                                                        }
                                                    }
                                                }"
                                            >
                                                <div class="relative">
                                                    <!-- Light version (default) -->
                                                    <img 
                                                        x-ref="previewImg"
                                                        src="/images/stylized-bg-'.($enabled ? 'enabled' : 'disabled').'-sample-light.png"
                                                        alt="Background Preview"
                                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:opacity-80 transition-opacity duration-200 dark:hidden"
                                                        style="aspect-ratio: 1919/991; object-fit: contain;"
                                                        @click="window.open($refs.previewImg.src, \'_blank\')"
                                                    />
                                                    <!-- Dark version -->
                                                    <img 
                                                        x-ref="previewImgDark"
                                                        src="/images/stylized-bg-'.($enabled ? 'enabled' : 'disabled').'-sample-dark.png"
                                                        alt="Background Preview"
                                                        class="w-full rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:opacity-80 transition-opacity duration-200 hidden dark:block"
                                                        style="aspect-ratio: 1919/991; object-fit: contain;"
                                                        @click="window.open($refs.previewImgDark.src, \'_blank\')"
                                                    />
                                                    <div class="absolute top-2 left-2 bg-primary-500/90 dark:bg-primary-500/90 px-2 py-1 rounded-full text-xs font-medium">
                                                        <span x-text="enabled ? enabledText : disabledText" class="text-primary-900"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        ');
                                    })
                                    ->visible(fn () => true),
                            ])
                            ->columns(1),
                    ])
                    ->collapsible()
                    ->collapsed()
                    ->columns(1),

                Forms\Components\Section::make(__('user.section.connection_settings'))
                    ->description(__('user.section.connection_settings_description'))
                    ->schema([
                        // Google connection fieldset
                        Forms\Components\Fieldset::make(new \Illuminate\Support\HtmlString(
                            '<div class="flex items-center gap-2">
                                <img src="'.asset('images/google-icon.svg').'" alt="Google" class="w-5 h-5">
                                <span>Google oAuth</span>
                            </div>'
                        ))
                            ->schema([
                                Forms\Components\Placeholder::make('google_status')
                                    ->label(__('user.form.connection_status'))
                                    ->content(function () {
                                        $user = auth()->user();
                                        if ($user->hasGoogleAuth()) {
                                            return new \Illuminate\Support\HtmlString(
                                                '<div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        '.__('user.form.connected').'
                                                    </span>
                                                </div>'
                                            );
                                        }

                                        return new \Illuminate\Support\HtmlString(
                                            '<div class="flex items-center gap-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    '.__('user.form.not_connected').'
                                                </span>
                                            </div>'
                                        );
                                    })
                                    ->columnSpan(2),

                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('connect_google')
                                        ->label(__('user.form.connect_google'))
                                        ->color('primary')
                                        ->icon('heroicon-o-link')
                                        ->visible(fn () => ! auth()->user()->hasGoogleAuth())
                                        ->requiresConfirmation()
                                        ->modalIcon('heroicon-o-link')
                                        ->modalHeading(__('user.form.connect_google'))
                                        ->modalDescription(__('user.form.google_description'))
                                        ->modalSubmitActionLabel(__('user.form.connect_google'))
                                        ->modalCancelActionLabel(__('user.form.cancel'))
                                        ->modalWidth('md')
                                        ->action(function () {
                                            $this->openGoogleAuthPopup();
                                        }),

                                    Forms\Components\Actions\Action::make('disconnect_google')
                                        ->label(__('user.form.disconnect_google'))
                                        ->color('danger')
                                        ->outlined()
                                        ->icon('heroicon-o-link-slash')
                                        ->visible(fn () => auth()->user()->hasGoogleAuth())
                                        ->requiresConfirmation()
                                        ->modalIcon('heroicon-o-link-slash')
                                        ->modalHeading(__('user.form.disconnect_google_confirm'))
                                        ->modalDescription(__('user.form.disconnect_google_description'))
                                        ->modalSubmitActionLabel(__('user.form.disconnect'))
                                        ->modalCancelActionLabel(__('user.form.cancel'))
                                        ->modalWidth('md')
                                        ->action(function () {
                                            $this->confirmDisconnectGoogle();
                                        }),
                                ])
                                    ->columnSpan(1)
                                    ->alignment(Alignment::End),
                            ])
                            ->columns(columns: 3)
                            ->columnSpanFull(),

                        Forms\Components\Fieldset::make(new \Illuminate\Support\HtmlString(
                            '<div class="flex items-center gap-2">
                                    <img src="'.asset('images/microsoft-icon.svg').'" alt="Microsoft" class="w-5 h-5">
                                    <span>Microsoft oAuth</span>
                                </div>'
                        ))
                            ->schema([
                                Forms\Components\Placeholder::make('microsoft_status')
                                    ->label(__('user.form.connection_status'))
                                    ->content(function () {
                                        return new \Illuminate\Support\HtmlString(
                                            '<div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                                        </svg>
                                                        '.__('user.form.microsoft_coming_soon').'
                                                    </span>
                                                </div>'
                                        );
                                    })
                                    ->columnSpan(2),

                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('connect_microsoft')
                                        ->label(__('user.form.connect_microsoft'))
                                        ->color('gray')
                                        ->outlined()
                                        ->icon('heroicon-o-link')
                                        ->disabled()
                                        ->requiresConfirmation(false),
                                    // ->modalIcon('heroicon-o-link')
                                    // ->modalHeading(__('user.form.microsoft_coming_soon'))
                                    // ->modalDescription(__('user.form.microsoft_coming_soon_description'))
                                    // ->modalSubmitActionLabel(__('user.form.connect_microsoft'))
                                    // ->modalCancelActionLabel(__('user.form.cancel'))
                                    // ->modalWidth('md')
                                    // ->action(function () {
                                    //     // Coming soon functionality
                                    // }),
                                ])
                                    ->columnSpan(1)
                                    ->alignment(Alignment::End),
                            ])
                            ->columns(columns: 3)
                            ->columnSpanFull(),

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

    public function save(): void
    {
        $user = auth()->user();
        $formData = $this->form->getState();

        // Store the original status before saving
        $originalStatus = $user->online_status;
        $newStatus = $formData['online_status'] ?? null;

        try {
            $this->beginDatabaseTransaction();

            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $this->handleRecordUpdate($this->getUser(), $data);

            // Skip parent afterSave hook to prevent duplicate notifications
            // $this->callHook('afterSave');
        } catch (\Filament\Support\Exceptions\Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction() ?
                $this->rollBackDatabaseTransaction() :
                $this->commitDatabaseTransaction();

            return;
        } catch (\Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        if (request()->hasSession() && array_key_exists('password', $data)) {
            request()->session()->put([
                'password_hash_'.\Filament\Facades\Filament::getAuthGuard() => $data['password'],
            ]);
        }

        $this->data['password'] = null;
        $this->data['passwordConfirmation'] = null;

        // Check if online status was changed and trigger presence update
        if (isset($newStatus) && $originalStatus !== $newStatus) {
            // Use presence status manager to handle the status change
            \App\Services\OnlineStatus\PresenceStatusManager::handleManualChange($user, $newStatus);
        }

        // Call afterSave for other logic (this will send our custom notification)
        $this->afterSave();
    }

    public function afterSave(): void
    {
        $formData = $this->form->getState();

        // If user changed the password, log them out
        if (filled($formData['password'] ?? null)) {
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
        if (! $user) {
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
     * Show success notification for Google connection
     */
    public function showGoogleConnectionSuccess(): void
    {
        Notification::make()
            ->title(__('user.form.google_connected'))
            ->body(__('user.form.google_connected_body'))
            ->success()
            ->send();
    }

    /**
     * Show error notification for Google connection
     */
    public function showGoogleConnectionError(string $message): void
    {
        Notification::make()
            ->title(__('user.form.google_connection_failed'))
            ->body($message)
            ->danger()
            ->send();
    }

    /**
     * Open Google OAuth popup window for account connection
     */
    public function openGoogleAuthPopup(): void
    {
        $this->js('
            const popup = window.open(
                "'.route('auth.google', ['source' => 'profile']).'",
                "googleSignIn",
                "width=460,height=800,scrollbars=yes,resizable=yes,top=" +
                    Math.max(0, (screen.height - 800) / 2) +
                    ",left=" +
                    Math.max(0, (screen.width - 460) / 2)
            );
            
            if (!popup) {
                alert("Popup window was blocked. Please allow popups for this site.");
                return;
            }
            
            // Listen for messages from the popup
            const messageListener = (event) => {
                if (event.origin !== window.location.origin) return;
                
                if (event.data.success === true) {
                    popup.close();
                    window.removeEventListener("message", messageListener);
                    // Show success notification using custom notification system
                    if (typeof showSuccessNotification === "function") {
                        showSuccessNotification(event.data.message || "Google account connected successfully!");
                    } else if (typeof showNotification === "function") {
                        showNotification("success", event.data.message || "Google account connected successfully!");
                    }
                    // Store success in session storage and redirect
                    sessionStorage.setItem("google_connection_success", "true");
                    window.location.href = "'.route('filament.admin.auth.profile').'";
                } else if (event.data.success === false) {
                    popup.close();
                    window.removeEventListener("message", messageListener);
                    // Show error notification using custom notification system
                    if (typeof showErrorNotification === "function") {
                        showErrorNotification(event.data.message || "Failed to connect Google account");
                    } else if (typeof showNotification === "function") {
                        showNotification("error", event.data.message || "Failed to connect Google account");
                    } else {
                        // Fallback to Livewire notification
                        $wire.call("showGoogleConnectionError", event.data.message || "Failed to connect Google account");
                    }
                }
            };
            
            window.addEventListener("message", messageListener);
            
            // Check if popup was closed manually
            const checkClosed = setInterval(function () {
                if (popup.closed) {
                    clearInterval(checkClosed);
                    window.removeEventListener("message", messageListener);
                }
            }, 1000);
        ');
    }
}
