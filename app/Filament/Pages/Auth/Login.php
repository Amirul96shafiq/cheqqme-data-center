<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use FIlament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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
                    ->label(__('login.form.email'))
                    ->autocomplete('email')
                    ->required()
                    ->autofocus(),

                Forms\Components\TextInput::make('password')
                    ->label(__('login.form.password'))
                    ->password()
                    ->required()
                    ->revealable()
                    ->autocomplete('password'),

                Forms\Components\Checkbox::make('remember')
                    ->label(__('login.form.remember'))
                    ->columnSpanFull(),

                Forms\Components\Actions::make([
                    Action::make('login_button')
                        ->label(__('login.actions.login'))
                        ->submit('login')
                        ->extraAttributes(['class' => 'w-full py-4']),

                    Action::make('forgotPassword')
                        ->label(__('login.actions.forgotPassword'))
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

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        $loginField = filter_var($data['email'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $data['email'],
            'password' => $data['password'],
        ];

        if (!Auth::attempt($credentials, $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        return new class implements LoginResponse {
            public function toResponse($request)
            {
                return redirect()->route('filament.admin.pages.dashboard');
            }
        };
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    public function mount(): void
    {
        // dd('Custom Login Loaded'); // For testing loading
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
