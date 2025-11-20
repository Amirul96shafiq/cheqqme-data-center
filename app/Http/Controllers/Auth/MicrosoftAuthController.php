<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Microsoft\Provider as BaseMicrosoftProvider;

// Custom Microsoft provider that uses 'common' tenant for both organizational and personal accounts
class CustomMicrosoftProvider extends BaseMicrosoftProvider
{
    protected function getTokenUrl(): string
    {
        return sprintf('https://login.microsoftonline.com/%s/oauth2/v2.0/token', 'common');
    }

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            sprintf(
                'https://login.microsoftonline.com/%s/oauth2/v2.0/authorize',
                'common'
            ),
            $state
        );
    }

    public function getLogoutUrl(?string $redirectUri = null)
    {
        $logoutUrl = sprintf('https://login.microsoftonline.com/%s/oauth2/v2.0/logout', 'common');

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

        // Use custom Microsoft provider that uses 'common' tenant for both org and personal accounts
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

        // Request basic scopes (OnlineMeetings.ReadWrite commented out for testing without admin consent)
        $driver->scopes([
            'openid',
            'profile',
            'email',
            'User.Read',
            // 'OnlineMeetings.ReadWrite', // TODO: Requires admin consent - re-enable when ready for production
            'offline_access', // Required to get refresh token
        ]);

        $redirectUrl = $driver->redirect()->getTargetUrl();

        // Debug: Log the redirect URL
        \Log::info('Microsoft OAuth redirect URL: '.$redirectUrl);

        return redirect($redirectUrl);
    }

    /**
     * Handle Microsoft OAuth callback
     * Processes the OAuth response, authenticates the user, and redirects appropriately
     */
    public function handleMicrosoftCallback(Request $request)
    {
        // If this is accessed directly in a browser (popup), show the popup callback view
        if (! $request->ajax() && ! $request->wantsJson()) {
            return view('auth.microsoft-popup-callback');
        }

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

            // Determine redirect URL based on session source (used for both success and error cases)
            $source = session('microsoft_oauth_source', 'login');
            $isFromProfile = $source === 'profile';
            $redirectUrl = $isFromProfile
                ? route('filament.admin.auth.profile')
                : route('filament.admin.pages.dashboard');

            $microsoftEmail = $microsoftUser->getEmail();

            if ($isFromProfile) {
                // User is connecting their account - verify email matches
                $currentUser = Auth::user();
                
                if (! $currentUser) {
                    // Should not happen if source is profile, but handle gracefully
                    return response()->json([
                        'success' => false,
                        'message' => 'Session expired. Please login again.',
                        'redirect_url' => route('filament.admin.auth.login'),
                    ], 401);
                }

                if (strtolower($currentUser->email) !== strtolower($microsoftEmail)) {
                    session()->forget('microsoft_oauth_source');
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'The email address of the Microsoft account (' . $microsoftEmail . ') does not match your current account\'s email. Please connect with the correct account.',
                        'redirect_url' => $redirectUrl,
                    ], 400);
                }

                $user = $currentUser;
            } else {
                // Login flow - check if user exists by email
                $user = User::where('email', $microsoftEmail)->first();

                if (! $user) {
                    // Clear the session data even on error to avoid stale state
                    session()->forget('microsoft_oauth_source');
    
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to login. The selected Microsoft Account does not exist in the system.',
                        'redirect_url' => $redirectUrl,
                    ], 404);
                }
            }

            // Update Microsoft ID and connection date (only if not already connected)
            $updateData = [
                'microsoft_id' => $microsoftUser->getId(),
                'microsoft_token' => $microsoftUser->token, // This is already the JWT access token string
                'microsoft_refresh_token' => $microsoftUser->refreshToken,
            ];

            if (! $user->microsoft_connected_at) {
                $updateData['microsoft_connected_at'] = now();
            }

            $user->update($updateData);

            // Update Microsoft avatar only if no custom avatar exists
            // Wrap in try-catch to handle SSL certificate issues in development
            try {
                $user->updateMicrosoftAvatar($microsoftUser->getAvatar());
            } catch (\Exception $e) {
                // Log the error but don't fail the authentication
                \Log::warning('Failed to fetch Microsoft avatar: '.$e->getMessage());
                // Continue without avatar - authentication still succeeds
            }

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
