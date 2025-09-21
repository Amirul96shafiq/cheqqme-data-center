<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Microsoft\Provider as BaseMicrosoftProvider;

// Custom Microsoft provider that uses 'consumers' tenant instead of 'common'
class CustomMicrosoftProvider extends BaseMicrosoftProvider
{
    protected function getTokenUrl(): string
    {
        return sprintf('https://login.microsoftonline.com/%s/oauth2/v2.0/token', 'consumers');
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            sprintf(
                'https://login.microsoftonline.com/%s/oauth2/v2.0/authorize',
                'consumers'
            ),
            $state
        );
    }

    public function getLogoutUrl(?string $redirectUri = null)
    {
        $logoutUrl = sprintf('https://login.microsoftonline.com/%s/oauth2/v2.0/logout', 'consumers');

        return $redirectUri === null ?
            $logoutUrl :
            $logoutUrl.'?post_logout_redirect_uri='.urlencode($redirectUri);
    }
}

class MicrosoftAuthController extends Controller
{
    /**
     * Redirect to Microsoft OAuth
     * Stores the source (login/profile) in session for proper redirect after callback
     */
    public function redirectToMicrosoft(Request $request)
    {
        $source = $request->get('source', 'login');
        session(['microsoft_oauth_source' => $source]);

        // For production, use proper SSL verification:
        // return Socialite::driver('microsoft')->redirect();

        // Use custom Microsoft provider that uses 'consumers' tenant
        $driver = new CustomMicrosoftProvider(
            request(),
            config('services.microsoft.client_id'),
            config('services.microsoft.client_secret'),
            config('services.microsoft.redirect')
        );

        // For development (Windows cURL certificate issue), disable SSL verification:
        $driver->setHttpClient(new \GuzzleHttp\Client([
            'verify' => false,
        ]));

        return redirect($driver->redirect()->getTargetUrl());
    }

    /**
     * Handle Microsoft OAuth callback
     * Processes the OAuth response, authenticates the user, and redirects appropriately
     */
    public function handleMicrosoftCallback(Request $request)
    {
        try {
            // For production, use proper SSL verification:
            // $microsoftUser = Socialite::driver('microsoft')->user();

            // Use custom Microsoft provider that uses 'consumers' tenant
            $driver = new CustomMicrosoftProvider(
                request(),
                config('services.microsoft.client_id'),
                config('services.microsoft.client_secret'),
                config('services.microsoft.redirect')
            );

            // For development (Windows cURL certificate issue), disable SSL verification:
            $driver->setHttpClient(new \GuzzleHttp\Client([
                'verify' => false,
            ]));

            $microsoftUser = $driver->user();

            // Check if user exists by email
            $user = User::where('email', $microsoftUser->getEmail())->first();

            // Determine redirect URL based on session source (used for both success and error cases)
            $source = session('microsoft_oauth_source', 'login');
            $isFromProfile = $source === 'profile';
            $redirectUrl = $isFromProfile
                ? route('filament.admin.auth.profile')
                : route('filament.admin.pages.dashboard');

            if (! $user) {
                // Clear the session data even on error to avoid stale state
                session()->forget('microsoft_oauth_source');

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to login. The selected Microsoft Account does not exist in the system.',
                    'redirect_url' => $redirectUrl,
                ], 404);
            }

            // Update Microsoft ID and avatar (if no custom avatar)
            $user->update([
                'microsoft_id' => $microsoftUser->getId(),
            ]);

            // Update Microsoft avatar only if no custom avatar exists
            $user->updateMicrosoftAvatar($microsoftUser->getAvatar());

            // Log the user in
            Auth::login($user);

            $message = $isFromProfile
                ? 'Microsoft account connected successfully!'
                : 'Successfully signed in with Microsoft!';

            // Clear the session data
            session()->forget('microsoft_oauth_source');

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => $redirectUrl,
            ]);

        } catch (\Exception $e) {
            // On exception, try to include redirect context too
            $source = session('microsoft_oauth_source', 'login');
            $isFromProfile = $source === 'profile';
            $redirectUrl = $isFromProfile
                ? route('filament.admin.auth.profile')
                : route('filament.admin.pages.dashboard');

            // Clear the session data
            session()->forget('microsoft_oauth_source');

            // Log the detailed error for debugging
            \Log::error('Microsoft OAuth Error: '.$e->getMessage(), [
                'exception' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate with Microsoft. Please try again. Error: '.$e->getMessage(),
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
        return view('auth.microsoft-popup-callback');
    }
}
