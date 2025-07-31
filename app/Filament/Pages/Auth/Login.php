<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use FIlament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Redirect;
use Filament\Forms\Components\Actions\Action;


class Login extends BaseLogin
{
    protected static bool $shouldRegisterNavigation = false;

    // Remove Default Form Action (Form buttons)
    protected function getFormActions(): array
    {
        return [];
    }

    // Styling customise Login Form
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')
                    ->label('Email Address')
                    ->email()
                    ->autocomplete('email')
                    ->required()
                    ->autofocus(),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required()
                    ->revealable()
                    ->autocomplete('password'),

                Forms\Components\Checkbox::make('remember')
                    ->label('Remember Me')
                    ->columnSpanFull(),

                Forms\Components\Actions::make([
                    Action::make('login_button')
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
    public function mount(): void
    {
        //dd('Custom Login Loaded'); // For testing loading
        parent::mount();

        $status = session()->pull('status');

        if ($status) {
            Notification::make()
                ->title($status)
                ->success()
                ->duration(5000)
                ->send();
        }
    }
}
