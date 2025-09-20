<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use FIlament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
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

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Checkbox::make('remember')
                            ->label(__('login.form.remember'))
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('forgot_password')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="flex justify-end mt-0">
                                    <a href="'.route('password.request').'" 
                                        class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:underline transition-colors duration-200">
                                        '.__('login.actions.forgotPassword').'
                                    </a>
                                </div>'
                            ))
                            ->columnSpan(1),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Actions::make([
                    Action::make('login_button')
                        ->label(__('login.actions.login'))
                        ->submit('login')
                        ->extraAttributes(['class' => 'w-full py-4']),
                ])
                    ->columnSpanFull()
                    ->columns(1),

                // Separator between login methods
                Forms\Components\Placeholder::make('separator')
                    ->label('')
                    ->content(new \Illuminate\Support\HtmlString(
                        '<div class="flex items-center justify-center my-4">
                            <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
                            <span class="px-4 text-[10px] font-light text-gray-500 dark:text-gray-400">'.__('login.form.or').'</span>
                            <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
                        </div>'
                    ))
                    ->columnSpanFull(),

                Forms\Components\Actions::make([
                    // Google Sign-in button - opens popup window for OAuth authentication
                    Action::make('google_signin')
                        ->view('components.google-signin-button'),
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

        if (! Auth::attempt($credentials, $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        return new class implements LoginResponse
        {
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

        // Check for Google sign-in errors
        $googleError = session()->pull('google_error');
        if ($googleError) {
            Notification::make()
                ->title($googleError)
                ->danger()
                ->duration(5000)
                ->send();
        }
    }

    /**
     * Handle Google sign-in error
     */
    public function handleGoogleError(string $message): void
    {
        Notification::make()
            ->title($message)
            ->danger()
            ->duration(5000)
            ->send();
    }
}
