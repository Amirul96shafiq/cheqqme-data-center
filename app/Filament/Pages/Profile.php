<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Forms\Components\Actions\Action;
use Filament\Notifications\Notification;

class Profile extends EditProfile
{
    public string $old_password = ''; // For old password input

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profile Settings')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->image()
                            ->imageEditor()
                            ->circleCropper()
                            ->directory('avatars')
                            ->moveFiles()
                            ->columnSpanFull()
                            ->afterStateUpdated(function ($state) {
                                if (!$state instanceof TemporaryUploadedFile)
                                    return;

                                // Delete old avatar if exists
                                $oldAvatar = auth()->user()->avatar;
                                if ($oldAvatar && Storage::exists($oldAvatar)) {
                                    Storage::delete($oldAvatar);
                                }
                            }),
                        Forms\Components\TextInput::make('username')->label('Username')->required()->maxLength(20),
                        $this->getNameFormComponent()->nullable(),
                        $this->getEmailFormComponent()->label('Email'),
                        //$this->getPasswordFormComponent(),
                        //$this->getPasswordConfirmationFormComponent(),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Password Settings')
                    ->description('Leave blank if you don’t want to change your password')
                    ->schema([
                        // OLD password
                        Forms\Components\Fieldset::make('Old Password')
                            ->columns(1)
                            ->schema([
                                Forms\Components\TextInput::make('old_password')
                                    ->label('Password')
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
                        Forms\Components\Fieldset::make('New Password')
                            ->columns(1)
                            ->schema([
                                // Generate password feature
                                Forms\Components\Actions::make([
                                    Action::make('generatePassword')
                                        ->label('Generate Strong Password')
                                        ->icon('heroicon-o-code-bracket-square')
                                        ->color('gray')
                                        ->action(function ($set) {
                                            $generated = str()->random(16);
                                            $set('password', $generated);
                                        }),
                                ]),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(fn($state) => filled($state))
                                    ->same('password_confirmation')
                                    ->minLength(5)
                                    ->columnSpanFull(),
                                // CONFIRM password
                                Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Confirm Password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(fn($state) => filled($state))
                                    ->columnSpanFull(),
                            ]),
                    ])
            ]);
    }

    public function afterSave(): void
    {
        // If user changed the password, log them out
        if (filled($this->form->getState()['password'] ?? null)) {
            Notification::make()
                ->title('Saved. Please re-login or refresh the page.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Saved.')
                ->success()
                ->send();
        }
    }

    // Disabled Default notification in Profile
    protected function getSavedNotification(): ?Notification
    {
        return null;
    }
}