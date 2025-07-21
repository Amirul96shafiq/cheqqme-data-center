<?php

namespace App\Filament\Pages;

use Filament\Forms;
use FIlament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\Actions\Action;


class Login extends BaseLogin
{
    protected function getFormActions(): array
    {
        return [];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->required()
                    ->autofocus(),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(),

                Forms\Components\Actions::make([
                    Action::make('login')
                        ->label('Login')
                        ->submit('login')
                        ->extraAttributes(['class' => 'w-full py-4']),

                    Action::make('forgotPassword')
                        ->label('Forgot Password?')
                        ->url(route('password.request'))
                        ->color('gray')
                        ->link()
                        ->extraAttributes([
                            'class' => 'w-full text-center mt-2 text-sm',
                        ]),
                ])
                    ->columnSpanFull()
                    ->columns(1),
            ]);
    }
}