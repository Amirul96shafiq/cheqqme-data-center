<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleMeetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    protected GoogleMeetService $googleMeetService;

    public function __construct(GoogleMeetService $googleMeetService)
    {
        $this->googleMeetService = $googleMeetService;
    }

    /**
     * Redirect to Google Calendar OAuth
     */
    public function redirectToGoogleCalendar(Request $request)
    {
        $state = $request->get('state', 'meeting_link');
        session(['google_calendar_state' => $state]);

        // Store the referring URL to redirect back after OAuth
        $referrer = $request->headers->get('referer');
        if ($referrer) {
            session(['google_calendar_referrer' => $referrer]);
        }

        $authUrl = $this->googleMeetService->getAuthUrl($state);

        return redirect($authUrl);
    }

    /**
     * Handle Google Calendar OAuth callback
     */
    public function handleGoogleCalendarCallback(Request $request)
    {
        try {
            $code = $request->get('code');
            $state = session('google_calendar_state', 'meeting_link');

            Log::info('Google Calendar callback received', [
                'has_code' => ! empty($code),
                'state' => $state,
                'user_id' => Auth::id(),
                'request_params' => $request->all(),
            ]);

            if (! $code) {
                $referrer = session('google_calendar_referrer');
                session()->forget('google_calendar_referrer');

                return $this->handleError('Authorization code not received', $referrer);
            }

            // Exchange code for token
            $token = $this->googleMeetService->exchangeCodeForToken($code);

            Log::info('Token exchange result', [
                'token_received' => ! empty($token),
                'token_keys' => $token ? array_keys($token) : null,
                'user_id' => Auth::id(),
            ]);

            if (! $token) {
                $referrer = session('google_calendar_referrer');
                session()->forget('google_calendar_referrer');

                return $this->handleError('Failed to exchange authorization code for token', $referrer);
            }

            // Store token in user's session or database
            session(['google_calendar_token' => $token]);

            // Update user's Google Calendar access
            $user = Auth::user();
            if ($user) {
                $updateResult = $user->update([
                    'google_calendar_token' => json_encode($token),
                    'google_calendar_connected_at' => now(),
                ]);

                Log::info('User token update result', [
                    'user_id' => $user->id,
                    'update_success' => $updateResult,
                    'token_saved' => ! is_null($user->fresh()->google_calendar_token),
                ]);
            } else {
                Log::error('No authenticated user found during callback');
            }

            Log::info('Google Calendar connected successfully', [
                'user_id' => Auth::id(),
                'state' => $state,
            ]);

            // Clear the referrer from session
            $referrer = session('google_calendar_referrer');
            session()->forget('google_calendar_referrer');

            // Redirect based on state
            return $this->redirectAfterAuth($state, $referrer);

        } catch (\Exception $e) {
            Log::error('Google Calendar OAuth callback failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            // Get referrer before clearing
            $referrer = session('google_calendar_referrer');
            session()->forget('google_calendar_referrer');

            return $this->handleError('Authentication failed: '.$e->getMessage(), $referrer);
        }
    }

    /**
     * Disconnect Google Calendar
     */
    public function disconnectGoogleCalendar()
    {
        try {
            $user = Auth::user();
            if ($user) {
                $user->update([
                    'google_calendar_token' => null,
                    'google_calendar_connected_at' => null,
                ]);
            }

            session()->forget('google_calendar_token');

            Log::info('Google Calendar disconnected', [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Google Calendar disconnected successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Google Calendar disconnect failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disconnect Google Calendar',
            ], 500);
        }
    }

    /**
     * Check Google Calendar connection status
     */
    public function checkConnectionStatus()
    {
        try {
            $user = Auth::user();
            $token = $user?->google_calendar_token;

            if (! $token) {
                return response()->json([
                    'connected' => false,
                    'message' => 'Not connected to Google Calendar',
                ]);
            }

            $this->googleMeetService->setAccessToken(json_decode($token, true));
            $isValid = $this->googleMeetService->hasValidAccess();

            return response()->json([
                'connected' => $isValid,
                'message' => $isValid ? 'Connected to Google Calendar' : 'Connection expired',
            ]);

        } catch (\Exception $e) {
            Log::error('Google Calendar connection check failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'connected' => false,
                'message' => 'Connection check failed',
            ], 500);
        }
    }

    /**
     * Handle authentication errors
     */
    private function handleError(string $message, ?string $referrer = null)
    {
        Log::error('Google Calendar authentication error: '.$message, [
            'user_id' => Auth::id(),
        ]);

        // Redirect back to referrer if available, otherwise to meeting links list
        $redirectUrl = $referrer ?: route('filament.admin.resources.meeting-links.index');

        return redirect($redirectUrl)
            ->with('error', 'Google Calendar authentication failed: '.$message);
    }

    /**
     * Redirect after successful authentication
     */
    private function redirectAfterAuth(string $state, ?string $referrer = null)
    {
        switch ($state) {
            case 'meeting_link':
                $redirectUrl = $referrer ?: route('filament.admin.resources.meeting-links.index');

                return redirect($redirectUrl)
                    ->with('success', 'Google Calendar connected successfully! You can now generate Google Meet links.');

            case 'profile':
                return redirect()->route('filament.admin.auth.profile')
                    ->with('success', 'Google Calendar connected successfully!');

            default:
                return redirect()->route('filament.admin.pages.dashboard')
                    ->with('success', 'Google Calendar connected successfully!');
        }
    }
}
