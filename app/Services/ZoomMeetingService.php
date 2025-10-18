<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ZoomMeetingService
{
    protected GuzzleClient $client;

    protected string $baseUri = 'https://api.zoom.us/v2/';

    protected ?string $accessToken = null;

    public function __construct()
    {
        $this->client = new GuzzleClient([
            'base_uri' => $this->baseUri,
            'timeout' => 30,
            'verify' => ! app()->environment('local'), // Disable SSL verification for local development
        ]);
    }

    /**
     * Set the access token for the authenticated user
     */
    public function setAccessToken(string|array $accessToken): void
    {
        if (is_array($accessToken)) {
            $this->accessToken = $accessToken['access_token'] ?? null;
        } else {
            $this->accessToken = $accessToken;
        }
    }

    /**
     * Generate a new Zoom meeting link
     */
    public function generateMeetingLink(string $title, ?string $startTime = null, ?int $duration = 60): ?array
    {
        try {
            if (! $this->accessToken) {
                Log::error('Zoom access token not set');

                return null;
            }

            // Get the user's Zoom user ID (use 'me' for the authenticated user)
            $userId = 'me';

            // Set default start time if not provided
            $startDateTime = $startTime ?? now()->addHour()->toIso8601String();

            // Basic Zoom meeting settings
            $zoomSettings = [
                'host_video' => true,
                'participant_video' => true,
                'join_before_host' => false,
                'watermark' => false,
                'audio' => 'both',
                'auto_recording' => 'none',
            ];

            // Log the settings being sent to Zoom
            Log::info('Creating Zoom meeting with settings', [
                'zoom_settings_sent' => $zoomSettings,
                'user_id' => Auth::id(),
            ]);

            // Create the meeting
            $response = $this->client->post("users/{$userId}/meetings", [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'topic' => $title,
                    'type' => 2, // Scheduled meeting
                    'start_time' => $startDateTime,
                    'duration' => $duration,
                    'timezone' => config('app.timezone'),
                    'settings' => $zoomSettings,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'meeting_url' => $data['join_url'] ?? null,
                'meeting_id' => (string) ($data['id'] ?? null),
                'host_email' => $data['host_email'] ?? null,
                'start_url' => $data['start_url'] ?? null,
                'password' => $data['password'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Zoom meeting generation failed: '.$e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return null;
        }
    }

    /**
     * Update an existing Zoom meeting
     */
    public function updateMeeting(string $meetingId, string $title, ?string $startTime = null, ?int $duration = 60): ?array
    {
        try {
            if (! $this->accessToken) {
                Log::error('Zoom access token not set');

                return null;
            }

            $updateData = [
                'topic' => $title,
            ];

            if ($startTime) {
                $updateData['start_time'] = $startTime;
            }

            if ($duration) {
                $updateData['duration'] = $duration;
            }

            $response = $this->client->patch("meetings/{$meetingId}", [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $updateData,
            ]);

            // Zoom PATCH returns 204 No Content on success
            if ($response->getStatusCode() === 204) {
                // Fetch the updated meeting details
                return $this->getMeeting($meetingId);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Zoom meeting update failed: '.$e->getMessage(), [
                'error' => $e->getMessage(),
                'meeting_id' => $meetingId,
                'user_id' => Auth::id(),
            ]);

            return null;
        }
    }

    /**
     * Get meeting details
     */
    public function getMeeting(string $meetingId): ?array
    {
        try {
            if (! $this->accessToken) {
                Log::error('Zoom access token not set');

                return null;
            }

            $response = $this->client->get("meetings/{$meetingId}", [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'meeting_url' => $data['join_url'] ?? null,
                'meeting_id' => (string) ($data['id'] ?? null),
                'host_email' => $data['host_email'] ?? null,
                'start_url' => $data['start_url'] ?? null,
                'password' => $data['password'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Zoom meeting fetch failed: '.$e->getMessage(), [
                'error' => $e->getMessage(),
                'meeting_id' => $meetingId,
                'user_id' => Auth::id(),
            ]);

            return null;
        }
    }

    /**
     * Delete a Zoom meeting
     */
    public function deleteMeeting(string $meetingId): bool
    {
        try {
            if (! $this->accessToken) {
                Log::error('Zoom access token not set');

                return false;
            }

            $response = $this->client->delete("meetings/{$meetingId}", [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
            ]);

            // Zoom DELETE returns 204 No Content on success
            return $response->getStatusCode() === 204;
        } catch (\Exception $e) {
            Log::error('Zoom meeting deletion failed: '.$e->getMessage(), [
                'error' => $e->getMessage(),
                'meeting_id' => $meetingId,
                'user_id' => Auth::id(),
            ]);

            return false;
        }
    }

    /**
     * Get the authorization URL for Zoom OAuth
     */
    public function getAuthUrl(?string $state = null): string
    {
        $params = [
            'response_type' => 'code',
            'client_id' => config('services.zoom.client_id'),
            'redirect_uri' => config('services.zoom.redirect_uri'),
        ];

        if ($state) {
            $params['state'] = $state;
        }

        return 'https://zoom.us/oauth/authorize?'.http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code): ?array
    {
        try {
            Log::info('Attempting Zoom token exchange', [
                'code_length' => strlen($code),
                'client_id' => config('services.zoom.client_id'),
            ]);

            $response = $this->client->post('https://zoom.us/oauth/token', [
                'headers' => [
                    'Authorization' => 'Basic '.base64_encode(
                        config('services.zoom.client_id').':'.config('services.zoom.client_secret')
                    ),
                ],
                'form_params' => [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => config('services.zoom.redirect_uri'),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            Log::info('Zoom token exchange response', [
                'has_token' => ! empty($data['access_token']),
                'token_type' => $data['token_type'] ?? null,
            ]);

            if (empty($data['access_token'])) {
                Log::error('Zoom OAuth token exchange failed: No access token in response');

                return null;
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Zoom OAuth token exchange exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): ?array
    {
        try {
            $response = $this->client->post('https://zoom.us/oauth/token', [
                'headers' => [
                    'Authorization' => 'Basic '.base64_encode(
                        config('services.zoom.client_id').':'.config('services.zoom.client_secret')
                    ),
                ],
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data['access_token'])) {
                Log::error('Zoom token refresh failed: No access token in response');

                return null;
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Zoom token refresh failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Check if user has valid Zoom access
     */
    public function hasValidAccess(): bool
    {
        try {
            if (! $this->accessToken) {
                return false;
            }

            // Test the token by fetching user info
            $response = $this->client->get('users/me', [
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            Log::error('Zoom access check failed: '.$e->getMessage());

            return false;
        }
    }
}
