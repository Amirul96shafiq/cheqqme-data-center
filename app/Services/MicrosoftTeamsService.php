<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MicrosoftTeamsService
{
    protected GuzzleClient $client;
    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->client = new GuzzleClient([
            'base_uri' => 'https://graph.microsoft.com/v1.0/',
            'verify' => app()->environment('local') ? false : true,
            'timeout' => 30,
        ]);
    }

    /**
     * Set the access token for the authenticated user
     */
    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Generate a new Microsoft Teams meeting link
     */
    public function generateMeetingLink(string $title, ?string $startTime = null, int $duration = 60): ?array
    {
        try {
            if (!$this->accessToken) {
                Log::error('Microsoft Teams generation failed: No access token set');
                return null;
            }

            // Set default times if not provided
            $startDateTime = $startTime ?? now()->addHour()->toIso8601String();
            $endDateTime = $startTime 
                ? \Carbon\Carbon::parse($startTime)->addMinutes($duration)->toIso8601String()
                : now()->addHour()->addMinutes($duration)->toIso8601String();

            // Create the online meeting
            $response = $this->client->post('me/onlineMeetings', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime,
                    'subject' => $title,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'meeting_url' => $data['joinWebUrl'] ?? null,
                'meeting_id' => $data['id'] ?? null,
                'conference_id' => $data['id'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Microsoft Teams generation failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return null;
        }
    }

    /**
     * Refresh access token if needed
     */
    public function refreshTokenIfNeeded(string $refreshToken): ?array
    {
        try {
            $response = $this->client->post('https://login.microsoftonline.com/common/oauth2/v2.0/token', [
                'form_params' => [
                    'client_id' => config('services.microsoft.client_id'),
                    'client_secret' => config('services.microsoft.client_secret'),
                    'refresh_token' => $refreshToken,
                    'grant_type' => 'refresh_token',
                    'scope' => 'https://graph.microsoft.com/OnlineMeetings.ReadWrite offline_access',
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'access_token' => $data['access_token'] ?? null,
                'refresh_token' => $data['refresh_token'] ?? $refreshToken,
                'expires_in' => $data['expires_in'] ?? 3600,
            ];
        } catch (\Exception $e) {
            Log::error('Microsoft token refresh failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete a Microsoft Teams meeting
     */
    public function deleteMeeting(string $meetingId): bool
    {
        try {
            if (!$this->accessToken) {
                Log::error('Microsoft Teams deletion failed: No access token set');
                return false;
            }

            $this->client->delete("me/onlineMeetings/{$meetingId}", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->accessToken,
                ],
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Microsoft Teams deletion failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'meeting_id' => $meetingId,
                'user_id' => Auth::id(),
            ]);

            return false;
        }
    }
}
