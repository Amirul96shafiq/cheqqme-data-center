<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class Profile extends EditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
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
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->columns(1),
            ]);
    }
}