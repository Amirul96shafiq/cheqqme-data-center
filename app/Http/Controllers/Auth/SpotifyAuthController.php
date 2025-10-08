<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SpotifyAuthController extends Controller
{
    /**
     * Redirect to Spotify OAuth
     * Stores the source (login/profile) in session for proper redirect after callback
     */
    public function redirectToSpotify(Request $request)
    {
        // Ensure user is authenticated
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please log in first before connecting your Spotify account.',
                'redirect_url' => route('filament.admin.auth.login'),
            ], 401);
        }

        $source = $request->get('source', 'profile');
        session(['spotify_oauth_source' => $source]);

        // Use the same callback URL for both regular and popup flows
        // The popup handling is done in the callback method based on the source

        return Socialite::driver('spotify')
            ->stateless()
            ->scopes([
                'user-read-currently-playing',
                'user-read-playback-state',
                'streaming',  // Required for Web Playback SDK
                'user-read-email',
                'user-read-private',
            ])
            ->redirect();
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

    public function handleSpotifyCallback(Request $request)
    {
        try {
            \Log::info('Spotify callback started', ['request' => $request->all()]);

            // Configure Socialite with custom HTTP client for local development
            if (app()->environment('local')) {
                $httpClient = new \GuzzleHttp\Client([
                    'verify' => false,
                    'timeout' => 30,
                    'connect_timeout' => 10,
                ]);
                Socialite::driver('spotify')->setHttpClient($httpClient);
            }

            // Handle OAuth state validation manually to avoid InvalidStateException
            $state = $request->get('state');
            $code = $request->get('code');

            \Log::info('Spotify OAuth parameters', [
                'state' => $state,
                'code' => $code ? 'present' : 'missing',
                'all_params' => $request->all(),
            ]);

            // For development, we'll skip strict state validation
            // In production, you should validate the state matches what was sent
            $spotifyUser = Socialite::driver('spotify')->stateless()->user();

            // Log the Spotify user data for debugging
            \Log::info('Spotify OAuth callback', [
                'spotify_user_id' => $spotifyUser->getId(),
                'email' => $spotifyUser->getEmail(),
                'name' => $spotifyUser->getName(),
                'nickname' => $spotifyUser->getNickname(),
                'avatar' => $spotifyUser->getAvatar(),
            ]);

            // Get the currently authenticated user (for connecting existing account to Spotify)
            $user = Auth::user();

            if (! $user) {
                // Clear the session data even on error to avoid stale state
                session()->forget('spotify_oauth_source');

                $redirectUrl = route('filament.admin.pages.dashboard');

                return response()->json([
                    'success' => false,
                    'message' => 'Please log in first before connecting your Spotify account.',
                    'redirect_url' => $redirectUrl,
                ], 401);
            }

            // Update Spotify ID, access token, and avatar (if no custom avatar)
            \Log::info('Updating user Spotify data', [
                'user_id' => $user->id,
                'spotify_id' => $spotifyUser->getId(),
                'spotify_token_present' => ! empty($spotifyUser->token),
                'spotify_refresh_token_present' => ! empty($spotifyUser->refreshToken),
            ]);

            $user->update([
                'spotify_id' => $spotifyUser->getId(),
                'spotify_access_token' => $spotifyUser->token ?? null,
                'spotify_refresh_token' => $spotifyUser->refreshToken ?? null,
            ]);

            // Update Spotify avatar only if no custom avatar exists
            $user->updateSpotifyAvatar($spotifyUser->getAvatar());

            // Determine redirect URL based on session source (used for both success and error cases)
            $source = session('spotify_oauth_source', 'profile');
            $isFromProfile = $source === 'profile';

            // For popup flow, redirect to popup callback view with success data
            if ($isFromProfile) {
                \Log::info('Setting up popup callback for profile flow', [
                    'user_id' => $user->id,
                    'spotify_id' => $spotifyUser->getId(),
                ]);

                // Clear the session data
                session()->forget('spotify_oauth_source');

                // Store success message in session for the profile page to show
                session()->flash('spotify_connection_success', $this->getLocalizedMessage('spotifyAccountConnected'));

                // Return the popup callback view directly with success data
                return view('auth.spotify-popup-callback', [
                    'oauth_success' => true,
                    'oauth_message' => $this->getLocalizedMessage('spotifyAccountConnected'),
                    'oauth_redirect_url' => 'http://localhost:8000/admin/profile',                      // route('filament.admin.auth.profile'),
                ]);
            }

            $redirectUrl = route('filament.admin.pages.dashboard');

            // Log the user in
            Auth::login($user);

            $message = $isFromProfile
                ? $this->getLocalizedMessage('spotifyAccountConnected')
                : $this->getLocalizedMessage('spotifySigninSuccess');

            // Clear the session data
            session()->forget('spotify_oauth_source');

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => $redirectUrl,
            ]);

        } catch (\Exception $e) {
            // Log the full exception for debugging
            \Log::error('Spotify OAuth exception', [
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            // On exception, try to include redirect context too
            $source = session('spotify_oauth_source', 'login');
            $isFromProfile = $source === 'profile';

            if ($isFromProfile) {
                // Clear the session data
                session()->forget('spotify_oauth_source');

                // Return the popup callback view directly with error data
                return view('auth.spotify-popup-callback', [
                    'oauth_error' => 'Spotify authentication failed: '.$e->getMessage(),
                ]);
            }

            $redirectUrl = route('filament.admin.pages.dashboard');

            // Clear the session data
            session()->forget('spotify_oauth_source');

            return response()->json([
                'success' => false,
                'message' => 'Spotify authentication failed: '.$e->getMessage(),
                'redirect_url' => $redirectUrl,
            ], 500);
        }
    }

    /**
     * Show popup callback page
     * Renders the HTML page that handles OAuth callback communication in popup windows
     */
    public function showPopupCallback(Request $request)
    {
        // If this is an AJAX request, process the OAuth callback
        if ($request->ajax() || $request->wantsJson()) {
            // Process the OAuth callback and return JSON
            return $this->handleSpotifyCallback($request);
        }

        // Otherwise, show the popup callback view
        return view('auth.spotify-popup-callback');
    }

    /**
     * Clear Spotify OAuth session data
     */
    public function clearSession()
    {
        session()->forget([
            'spotify_oauth_source',
            'spotify_oauth_success',
            'spotify_oauth_message',
            'spotify_oauth_redirect_url',
            'spotify_oauth_error',
        ]);

        return response()->json(['success' => true]);
    }
}
