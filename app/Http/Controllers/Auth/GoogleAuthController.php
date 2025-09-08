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
     * Redirect to Google OAuth (for popup)
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // User doesn't exist - return error
                return response()->json([
                    'success' => false,
                    'message' => 'No account found with this Google email address.',
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

            return response()->json([
                'success' => true,
                'message' => 'Successfully signed in with Google!',
                'redirect_url' => route('filament.admin.pages.dashboard'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate with Google. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle popup window callback (for JavaScript integration)
     */
    public function handlePopupCallback(Request $request)
    {
        try {
            \Log::info('Google OAuth popup callback started', [
                'request_params' => $request->all(),
                'user_agent' => $request->userAgent(),
            ]);

            $googleUser = Socialite::driver('google')->user();

            \Log::info('Google user data received in popup callback', [
                'google_id' => $googleUser->getId(),
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar(),
            ]);

            // Check if user exists by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                \Log::warning('No user found with Google email in popup callback', [
                    'email' => $googleUser->getEmail(),
                ]);

                return response()->json([
                    'type' => 'GOOGLE_SIGNIN_ERROR',
                    'message' => 'No account found with this Google email address.',
                ]);
            }

            \Log::info('User found in popup callback, updating Google data', [
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

            // Update Google ID and avatar (if no custom avatar)
            $user->update([
                'google_id' => $googleUser->getId(),
            ]);

            // Update Google avatar only if no custom avatar exists
            $user->updateGoogleAvatar($googleUser->getAvatar());

            // Log the user in
            Auth::login($user);

            \Log::info('User successfully logged in via Google popup callback', [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'type' => 'GOOGLE_SIGNIN_SUCCESS',
                'message' => 'Successfully signed in with Google!',
                'redirect_url' => route('filament.admin.pages.dashboard'),
            ]);

        } catch (\Exception $e) {
            \Log::error('Google OAuth popup callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'type' => 'GOOGLE_SIGNIN_ERROR',
                'message' => 'Failed to authenticate with Google. Please try again.',
            ]);
        }
    }

    /**
     * Show popup callback page
     */
    public function showPopupCallback()
    {
        return view('auth.google-popup-callback');
    }
}
