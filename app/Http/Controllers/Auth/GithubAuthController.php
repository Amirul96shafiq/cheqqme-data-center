<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GithubAuthController extends Controller
{
    /**
     * Redirect to GitHub OAuth
     * Stores the source (login/profile) in session for proper redirect after callback
     */
    public function redirectToGithub(Request $request)
    {
        $source = $request->get('source', 'login');
        session(['github_oauth_source' => $source]);

        return Socialite::driver('github')->redirect();
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

    public function handleGithubCallback(Request $request)
    {
        try {
            // Disable SSL verification for local development
            $httpClient = new \GuzzleHttp\Client(['verify' => false]);
            $githubUser = Socialite::driver('github')->setHttpClient($httpClient)->user();

            // Check if user exists by email
            $user = User::where('email', $githubUser->getEmail())->first();

            // Determine redirect URL based on session source (used for both success and error cases)
            $source = session('github_oauth_source', 'login');
            $isFromProfile = $source === 'profile';
            $redirectUrl = $isFromProfile
                ? route('filament.admin.auth.profile')
                : route('filament.admin.pages.dashboard');

            if (! $user) {
                // Clear the session data even on error to avoid stale state
                session()->forget('github_oauth_source');

                return response()->json([
                    'success' => false,
                    'message' => $this->getLocalizedMessage('githubAccountNotFound'),
                    'redirect_url' => $redirectUrl,
                ], 404);
            }

            // Update GitHub ID and connection date
            $user->update([
                'github_id' => $githubUser->getId(),
                'github_token' => $githubUser->token,
                'github_refresh_token' => $githubUser->refreshToken,
                'github_connected_at' => now(),
            ]);

            // Update GitHub avatar only if no custom avatar exists
            try {
                $user->updateGithubAvatar($githubUser->getAvatar());
            } catch (\Exception $e) {
                \Log::warning('Failed to fetch GitHub avatar: '.$e->getMessage());
            }

            // Log the user in
            Auth::login($user);

            $message = $isFromProfile
                ? $this->getLocalizedMessage('githubAccountConnected')
                : $this->getLocalizedMessage('githubSigninSuccess');

            // Clear the session data
            session()->forget('github_oauth_source');

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => $redirectUrl,
            ]);

        } catch (\Exception $e) {
            \Log::error('GitHub Auth Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // On exception, try to include redirect context too
            $source = session('github_oauth_source', 'login');
            $isFromProfile = $source === 'profile';
            $redirectUrl = $isFromProfile
                ? route('filament.admin.auth.profile')
                : route('filament.admin.pages.dashboard');

            // Clear the session data
            session()->forget('github_oauth_source');

            return response()->json([
                'success' => false,
                'message' => $this->getLocalizedMessage('githubAuthenticationFailed'),
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
        return view('auth.github-popup-callback');
    }
}
