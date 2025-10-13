<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Google\Service\Calendar\ConferenceData;
use Google\Service\Calendar\ConferenceSolutionKey;
use Google\Service\Calendar\CreateConferenceRequest;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleMeetService
{
    protected GoogleClient $client;

    public function __construct()
    {
        $this->client = new GoogleClient;
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.calendar_redirect'));
        $this->client->addScope(Calendar::CALENDAR_EVENTS);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');

        // Disable SSL verification for local development
        if (app()->environment('local')) {
            $this->client->setHttpClient(new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 30,
            ]));
        }
    }

    /**
     * Set the access token for the authenticated user
     */
    public function setAccessToken(string|array $accessToken): void
    {
        $this->client->setAccessToken($accessToken);
    }

    /**
     * Generate a new Google Meet link
     */
    public function generateMeetLink(string $title, ?string $startTime = null, ?string $endTime = null): ?array
    {
        try {
            $service = new Calendar($this->client);

            // Set default times if not provided
            $startDateTime = $startTime ?? now()->addHour()->toIso8601String();
            $endDateTime = $endTime ?? now()->addHours(2)->toIso8601String();

            // Create the event with Google Meet conference
            $event = new Event([
                'summary' => $title,
                'start' => new EventDateTime([
                    'dateTime' => $startDateTime,
                    'timeZone' => config('app.timezone'),
                ]),
                'end' => new EventDateTime([
                    'dateTime' => $endDateTime,
                    'timeZone' => config('app.timezone'),
                ]),
                'conferenceData' => new ConferenceData([
                    'createRequest' => new CreateConferenceRequest([
                        'requestId' => uniqid('meet_', true),
                        'conferenceSolutionKey' => new ConferenceSolutionKey([
                            'type' => 'hangoutsMeet',
                        ]),
                    ]),
                ]),
                'attendees' => [],
                'reminders' => [
                    'useDefault' => true,
                ],
            ]);

            // Create the event with conference data
            $createdEvent = $service->events->insert('primary', $event, [
                'conferenceDataVersion' => 1,
            ]);

            return [
                'meeting_url' => $createdEvent->getHangoutLink(),
                'meeting_id' => $createdEvent->getId(),
                'conference_id' => $createdEvent->getConferenceData()?->getConferenceId(),
                'entry_points' => $createdEvent->getConferenceData()?->getEntryPoints(),
            ];
        } catch (\Exception $e) {
            Log::error('Google Meet generation failed: '.$e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return null;
        }
    }

    /**
     * Update an existing Google Meet event
     */
    public function updateMeetEvent(string $eventId, string $title, ?string $startTime = null, ?string $endTime = null): ?array
    {
        try {
            $service = new Calendar($this->client);

            // Get the existing event
            $event = $service->events->get('primary', $eventId);

            // Update the event
            $event->setSummary($title);

            if ($startTime) {
                $event->setStart(new EventDateTime([
                    'dateTime' => $startTime,
                    'timeZone' => config('app.timezone'),
                ]));
            }

            if ($endTime) {
                $event->setEnd(new EventDateTime([
                    'dateTime' => $endTime,
                    'timeZone' => config('app.timezone'),
                ]));
            }

            $updatedEvent = $service->events->update('primary', $eventId, $event);

            return [
                'meeting_url' => $updatedEvent->getHangoutLink(),
                'meeting_id' => $updatedEvent->getId(),
            ];
        } catch (\Exception $e) {
            Log::error('Google Meet update failed: '.$e->getMessage(), [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
                'user_id' => Auth::id(),
            ]);

            return null;
        }
    }

    /**
     * Delete a Google Meet event
     */
    public function deleteMeetEvent(string $eventId): bool
    {
        try {
            $service = new Calendar($this->client);
            $service->events->delete('primary', $eventId);

            return true;
        } catch (\Exception $e) {
            Log::error('Google Meet deletion failed: '.$e->getMessage(), [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
                'user_id' => Auth::id(),
            ]);

            return false;
        }
    }

    /**
     * Get the authorization URL for Google Calendar access
     */
    public function getAuthUrl(?string $state = null): string
    {
        if ($state) {
            $this->client->setState($state);
        }

        return $this->client->createAuthUrl();
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code): ?array
    {
        try {
            Log::info('Attempting token exchange', [
                'code_length' => strlen($code),
                'client_id' => config('services.google.client_id'),
            ]);

            $token = $this->client->fetchAccessTokenWithAuthCode($code);

            Log::info('Token exchange response', [
                'has_token' => ! empty($token),
                'token_keys' => $token ? array_keys($token) : null,
                'has_error' => isset($token['error']),
            ]);

            if (isset($token['error'])) {
                Log::error('Google OAuth token exchange failed: '.$token['error']);

                return null;
            }

            return $token;
        } catch (\Exception $e) {
            Log::error('Google OAuth token exchange exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Refresh access token if needed
     */
    public function refreshTokenIfNeeded(): bool
    {
        try {
            if ($this->client->isAccessTokenExpired()) {
                $refreshToken = $this->client->getRefreshToken();
                if ($refreshToken) {
                    $this->client->fetchAccessTokenWithRefreshToken($refreshToken);

                    return true;
                }

                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Google token refresh failed: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Check if user has valid Google Calendar access
     */
    public function hasValidAccess(): bool
    {
        try {
            $accessToken = $this->client->getAccessToken();
            if (! $accessToken) {
                return false;
            }

            if ($this->client->isAccessTokenExpired()) {
                return $this->refreshTokenIfNeeded();
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Google Calendar access check failed: '.$e->getMessage());

            return false;
        }
    }
}
