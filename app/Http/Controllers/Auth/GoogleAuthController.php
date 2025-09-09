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
     * Handle Google OAuth callback
     * Processes the OAuth response, authenticates the user, and redirects appropriately
     */
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

            if (!$user) {
                // Clear the session data even on error to avoid stale state
                session()->forget('google_oauth_source');

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to login. The selected Google Account does not exist in the system.',
                    'redirect_url' => $redirectUrl,
                ], 404);
            }

            // Update Google ID and avatar (if no custom avatar)
            $user->update([
                'google_id' => $googleUser->getId(),
            ]);

            // Update Google avatar only if no custom avatar exists
            $user->updateGoogleAvatar($googleUser->getAvatar());

            // Log the user in
            Auth::login($user);

            $message = $isFromProfile
                ? 'Google account connected successfully!'
                : 'Successfully signed in with Google!';

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
                'message' => 'Failed to authenticate with Google. Please try again.',
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
