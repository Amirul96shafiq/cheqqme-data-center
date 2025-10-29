<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use FIlament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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

                Forms\Components\Actions::make([
                    // Microsoft Sign-in button - opens popup window for OAuth authentication
                    Action::make('microsoft_signin')
                        ->view('components.microsoft-signin-button'),
                ])
                    ->columnSpanFull()
                    ->columns(1),
            ]);
    }

    public function authenticate(): ?LoginResponse
    {
        // SECURITY: Rate limiting per IP address
        $ipAddress = request()->ip();
        $rateLimitKey = 'login-attempt:'.$ipAddress;
        $maxAttempts = 5;
        $decaySeconds = 60; // 1 minute lockout

        // Check if too many attempts from this IP
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            $minutes = ceil($seconds / 60);

            throw ValidationException::withMessages([
                'data.email' => __('auth.throttle', ['seconds' => $seconds, 'minutes' => $minutes]),
            ]);
        }

        $data = $this->form->getState();

        // Determine login field: check if it's a phone number, email, or username
        $input = $data['email'];
        $loginField = 'username'; // default

        // Check if input is a phone number (contains only digits, potentially with + at start)
        if (preg_match('/^\+?\d+$/', $input)) {
            // Remove + if present and use phone field
            $loginField = 'phone';
            $input = preg_replace('/\D+/', '', $input); // Keep only digits
        } elseif (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $loginField = 'email';
        }

        $credentials = [
            $loginField => $input,
            'password' => $data['password'],
        ];

        if (! Auth::attempt($credentials, $data['remember'] ?? false)) {
            // SECURITY: Increment failed attempts
            RateLimiter::hit($rateLimitKey, $decaySeconds);
            $this->throwFailureValidationException();
        }

        // SECURITY: Clear rate limit on successful login
        RateLimiter::clear($rateLimitKey);

        // SECURITY: Explicitly clear remember_token if remember me is OFF
        // This ensures old remember sessions are invalidated
        $user = Auth::user();
        if ($user) {
            // Get remember value from form data (checkbox: 0 = unchecked, 1 = checked, null = not sent)
            $rememberValue = $data['remember'] ?? false;

            // Log for debugging
            \Log::info('Login remember token handling', [
                'user_id' => $user->id,
                'remember_value' => $rememberValue,
                'remember_type' => gettype($rememberValue),
            ]);

            // If remember me is NOT checked (false, 0, or null), clear the token
            if (! $rememberValue) {
                $oldToken = $user->remember_token;

                // Use direct update since remember_token is not in fillable
                \DB::table('users')
                    ->where('id', $user->id)
                    ->update(['remember_token' => null]);

                \Log::info('Remember token cleared', [
                    'user_id' => $user->id,
                    'old_token' => $oldToken,
                ]);
            }
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

        // Check for Microsoft "coming soon" notification
        $microsoftMessage = session()->pull('microsoft_coming_soon_message');
        if ($microsoftMessage) {
            Notification::make()
                ->title($microsoftMessage)
                ->info()
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

    /**
     * Handle Microsoft sign-in "coming soon" notification
     */
    public function handleMicrosoftComingSoon(string $message): void
    {
        Notification::make()
            ->title($message)
            ->info()
            ->duration(5000)
            ->send();
    }
}
