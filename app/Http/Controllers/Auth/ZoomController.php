<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\ZoomMeetingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ZoomController extends Controller
{
    protected ZoomMeetingService $zoomMeetingService;

    public function __construct(ZoomMeetingService $zoomMeetingService)
    {
        $this->zoomMeetingService = $zoomMeetingService;
    }

    /**
     * Redirect to Zoom OAuth
     */
    public function redirectToZoom(Request $request)
    {
        $state = $request->get('state', 'meeting_link');
        session(['zoom_state' => $state]);

        // Store the referring URL to redirect back after OAuth
        $referrer = $request->headers->get('referer');
        if ($referrer) {
            session(['zoom_referrer' => $referrer]);
        }

        $authUrl = $this->zoomMeetingService->getAuthUrl($state);

        return redirect($authUrl);
    }

    /**
     * Handle Zoom OAuth callback
     */
    public function handleZoomCallback(Request $request)
    {
        try {
            $code = $request->get('code');
            $state = session('zoom_state', 'meeting_link');

            Log::info('Zoom callback received', [
                'has_code' => ! empty($code),
                'state' => $state,
                'user_id' => Auth::id(),
                'request_params' => $request->all(),
            ]);

            if (! $code) {
                $referrer = session('zoom_referrer');
                session()->forget('zoom_referrer');

                return $this->handleError('Authorization code not received', $referrer);
            }

            // Exchange code for token
            $token = $this->zoomMeetingService->exchangeCodeForToken($code);

            Log::info('Token exchange result', [
                'token_received' => ! empty($token),
                'token_keys' => $token ? array_keys($token) : null,
                'user_id' => Auth::id(),
            ]);

            if (! $token) {
                $referrer = session('zoom_referrer');
                session()->forget('zoom_referrer');

                return $this->handleError('Failed to exchange authorization code for token', $referrer);
            }

            // Store token in user's session
            session(['zoom_token' => $token]);

            // Update user's Zoom access
            $user = Auth::user();
            if ($user) {
                $updateResult = $user->update([
                    'zoom_token' => json_encode($token),
                    'zoom_connected_at' => now(),
                ]);

                Log::info('User token update result', [
                    'user_id' => $user->id,
                    'update_success' => $updateResult,
                    'token_saved' => ! is_null($user->fresh()->zoom_token),
                ]);
            } else {
                Log::error('No authenticated user found during callback');
            }

            Log::info('Zoom connected successfully', [
                'user_id' => Auth::id(),
                'state' => $state,
            ]);

            // Clear the referrer from session
            $referrer = session('zoom_referrer');
            session()->forget('zoom_referrer');

            // Redirect based on state
            return $this->redirectAfterAuth($state, $referrer);

        } catch (\Exception $e) {
            Log::error('Zoom OAuth callback failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            // Get referrer before clearing
            $referrer = session('zoom_referrer');
            session()->forget('zoom_referrer');

            return $this->handleError('Authentication failed: '.$e->getMessage(), $referrer);
        }
    }

    /**
     * Disconnect Zoom
     */
    public function disconnectZoom()
    {
        try {
            $user = Auth::user();
            if ($user) {
                $user->update([
                    'zoom_token' => null,
                    'zoom_connected_at' => null,
                ]);
            }

            session()->forget('zoom_token');

            Log::info('Zoom disconnected', [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Zoom disconnected successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Zoom disconnect failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to disconnect Zoom',
            ], 500);
        }
    }

    /**
     * Check Zoom connection status
     */
    public function checkConnectionStatus()
    {
        try {
            $user = Auth::user();
            $token = $user?->zoom_token;

            if (! $token) {
                return response()->json([
                    'connected' => false,
                    'message' => 'Not connected to Zoom',
                ]);
            }

            $tokenData = json_decode($token, true);
            $this->zoomMeetingService->setAccessToken($tokenData);
            $isValid = $this->zoomMeetingService->hasValidAccess();

            return response()->json([
                'connected' => $isValid,
                'message' => $isValid ? 'Connected to Zoom' : 'Connection expired',
            ]);

        } catch (\Exception $e) {
            Log::error('Zoom connection check failed', [
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
        Log::error('Zoom authentication error: '.$message, [
            'user_id' => Auth::id(),
        ]);

        // Redirect back to referrer if available, otherwise to meeting links list
        $redirectUrl = $referrer ?: route('filament.admin.resources.meeting-links.index');

        return redirect($redirectUrl)
            ->with('error', 'Zoom authentication failed: '.$message);
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
                    ->with('success', 'Zoom connected successfully! You can now generate Zoom meeting links.');

            case 'profile':
                return redirect()->route('filament.admin.auth.profile')
                    ->with('success', 'Zoom connected successfully!');

            default:
                return redirect()->route('filament.admin.pages.dashboard')
                    ->with('success', 'Zoom connected successfully!');
        }
    }
}
