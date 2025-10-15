<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     * Stores the source (login/profile) in session for proper redirect after callback
     */
    public function redirectToGoogle(Request $request)
    {
        $source = $request->get('source', 'login');
        session(['google_oauth_source' => $source]);

        return Socialite::driver('google')->redirect();
    }

    /**
     * Get localized message based on user preference or session
     */
    private function getLocalizedMessage(string $key, string $defaultLocale = 'en'): string
    {
        // Try to get locale from session first
        $locale = session('locale', $defaultLocale);

        // Fallback to browser language detection if no session locale
        if ($locale === $defaultLocale) {
            $acceptLanguage = request()->header('Accept-Language', '');
            if (str_contains($acceptLanguage, 'ms') || str_contains($acceptLanguage, 'my')) {
                $locale = 'ms';
            }
        }

        // Set the locale for this request
        app()->setLocale($locale);

        // Return the translated message
        return __('login.errors.'.$key);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists by email
            $user = User::where('email', $googleUser->getEmail())->first();

            // Determine redirect URL based on session source (used for both success and error cases)
            $source = session('google_oauth_source', 'login');
            $isFromProfile = $source === 'profile';
            $redirectUrl = $isFromProfile
                ? route('filament.admin.auth.profile')
                : route('filament.admin.pages.dashboard');

            if (! $user) {
                // Clear the session data even on error to avoid stale state
                session()->forget('google_oauth_source');

                return response()->json([
                    'success' => false,
                    'message' => $this->getLocalizedMessage('googleAccountNotFound'),
                    'redirect_url' => $redirectUrl,
                ], 404);
            }

            // Update Google ID and connection date
            $user->update([
                'google_id' => $googleUser->getId(),
                'google_connected_at' => now(),
            ]);

            // Update Google avatar only if no custom avatar exists
            $user->updateGoogleAvatar($googleUser->getAvatar());

            // Log the user in
            Auth::login($user);

            $message = $isFromProfile
                ? $this->getLocalizedMessage('googleAccountConnected')
                : $this->getLocalizedMessage('googleSigninSuccess');

            // Clear the session data
            session()->forget('google_oauth_source');

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => $redirectUrl,
            ]);

        } catch (\Exception $e) {
            // On exception, try to include redirect context too
            $source = session('google_oauth_source', 'login');
            $isFromProfile = $source === 'profile';
            $redirectUrl = $isFromProfile
                ? route('filament.admin.auth.profile')
                : route('filament.admin.pages.dashboard');

            // Clear the session data
            session()->forget('google_oauth_source');

            return response()->json([
                'success' => false,
                'message' => $this->getLocalizedMessage('googleAuthenticationFailed'),
                'redirect_url' => $redirectUrl,
            ], 500);
        }
    }

    /**
     * Show popup callback page
     * Renders the HTML page that handles OAuth callback communication in popup windows
     */
    public function showPopupCallback()
    {
        return view('auth.google-popup-callback');
    }
}
