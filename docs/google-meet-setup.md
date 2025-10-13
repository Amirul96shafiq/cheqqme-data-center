# Google Meet Integration Setup Guide

This guide explains how to set up and use the Google Meet integration in the CheQQme Data Center application.

## Overview

The Google Meet integration allows users to:

-   Connect their Google Calendar account
-   Generate Google Meet links directly from the application
-   Automatically create calendar events with Google Meet conferencing
-   Manage meeting links associated with clients, projects, and documents

## Prerequisites

1. Google Cloud Console account
2. A Google Workspace or personal Google account
3. Composer package `google/apiclient` (already installed)

## Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the **Google Calendar API**:
    - Navigate to "APIs & Services" > "Library"
    - Search for "Google Calendar API"
    - Click "Enable"

## Step 2: Create OAuth 2.0 Credentials

1. Go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth client ID"
3. Choose "Web application" as the application type
4. Configure the OAuth client:

    - **Name**: CheQQme Data Center Google Calendar
    - **Authorized JavaScript origins**:
        - `http://localhost:8000` (development)
        - `https://yourdomain.com` (production)
    - **Authorized redirect URIs**:
        - `http://localhost:8000/auth/google/callback` (for Google Sign-in)
        - `http://localhost:8000/auth/google/calendar/callback` (for Google Calendar)
        - Add production URLs accordingly

5. Click "Create" and save your credentials

## Step 3: Configure Environment Variables

Update your `.env` file with the Google OAuth credentials:

```env
###############################################
# Google OAuth Configuration
###############################################
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/popup-callback
GOOGLE_CALENDAR_REDIRECT_URI=http://localhost:8000/auth/google/calendar/callback
```

**Note:** For production, update these URLs to match your production domain.

## Step 4: Run Database Migrations

The Google Calendar fields should already exist in the `users` table. Verify by running:

```bash
php artisan migrate:status
```

Look for the migration:

-   `2025_10_14_001245_add_google_calendar_fields_to_users_table.php`

If not migrated, run:

```bash
php artisan migrate
```

## Step 5: Connect Google Calendar Account

### For Users:

1. Log in to the application
2. Navigate to **Profile** page
3. Scroll to the **External Services** section
4. Find the **Google Calendar Connection** fieldset
5. Click **Connect Google Calendar**
6. You'll be redirected to Google's OAuth consent screen
7. Grant the required permissions:
    - View and manage your Google Calendar events
8. After authorization, you'll be redirected back to your profile

**Status indicators:**

-   🟢 **Connected**: Shows connection date
-   🔴 **Not Connected**: Ready to connect

### Disconnect Google Calendar:

1. Go to **Profile** > **External Services**
2. Click **Disconnect Google Calendar**
3. Confirm the action

**Note:** Disconnecting will prevent you from generating new Google Meet links, but existing links will remain functional.

## Step 6: Create Meeting Links

### Using the Meeting Links Resource:

1. Navigate to **Resources** > **Meeting Links**
2. Click **Create**
3. Fill in the meeting details:

    - **Meeting Title**: Enter a descriptive title
    - **Platform**: Select "Google Meet"
    - Click **Generate Google Meet URL**

4. If not connected to Google Calendar:

    - You'll see a notification to connect
    - Click the notification action to connect
    - Return and try generating again

5. Once generated:
    - The meeting URL will appear in the form
    - You can **Copy** the link
    - You can **Regenerate** if needed
    - Click **Save** to store the meeting link

### Meeting Link Features:

-   **Regenerate**: Create a new meeting link (deletes the old one)
-   **Delete Link**: Remove the meeting link and associated calendar event
-   **Copy to Clipboard**: Quickly copy the meeting URL
-   **Associate Resources**: Link clients, projects, documents, and users

### Additional Information Tab:

-   Add meeting description/notes
-   Add custom extra information fields
-   Invite users to the meeting

## How It Works

### Architecture:

1. **GoogleMeetService** (`app/Services/GoogleMeetService.php`):

    - Manages Google Calendar API interactions
    - Handles OAuth token management
    - Creates, updates, and deletes calendar events with Meet links

2. **GoogleCalendarController** (`app/Http/Controllers/Auth/GoogleCalendarController.php`):

    - Handles OAuth authentication flow
    - Stores Google Calendar tokens in user records
    - Manages connection/disconnection

3. **MeetingLinkResource** (`app/Filament/Resources/MeetingLinkResource.php`):

    - Filament resource for managing meeting links
    - Integrates with GoogleMeetService
    - Provides UI for generating and managing links

4. **Database**:
    - `users` table: Stores `google_calendar_token` and `google_calendar_connected_at`
    - `meeting_links` table: Stores meeting information and associations

### OAuth Flow:

```
User clicks "Connect Google Calendar"
    ↓
Redirect to Google OAuth (with Calendar API scope)
    ↓
User grants permissions
    ↓
Google redirects back with authorization code
    ↓
Exchange code for access/refresh tokens
    ↓
Store tokens in user record
    ↓
User can now generate Google Meet links
```

### Meeting Link Generation:

```
User clicks "Generate Google Meet URL"
    ↓
Check if user has valid Google Calendar token
    ↓
Use GoogleMeetService to create calendar event
    ↓
Calendar API returns event with Meet link
    ↓
Store meeting URL and ID in form
    ↓
User saves the meeting link record
```

## API Reference

### GoogleMeetService Methods:

#### `setAccessToken(string $accessToken): void`

Set the access token for the authenticated user.

#### `generateMeetLink(string $title, ?string $startTime = null, ?string $endTime = null): ?array`

Generate a new Google Meet link.

**Returns:**

```php
[
    'meeting_url' => 'https://meet.google.com/xxx-xxxx-xxx',
    'meeting_id' => 'google_calendar_event_id',
    'conference_id' => 'google_conference_id',
    'entry_points' => [...] // Array of entry points
]
```

#### `updateMeetEvent(string $eventId, string $title, ?string $startTime = null, ?string $endTime = null): ?array`

Update an existing Google Meet event.

#### `deleteMeetEvent(string $eventId): bool`

Delete a Google Meet event from Google Calendar.

#### `hasValidAccess(): bool`

Check if the user has valid Google Calendar access.

## Troubleshooting

### Error: "Access blocked: CheQQme Data Center has not completed the Google verification process"

**Solution:** This is the most common issue when setting up Google OAuth for the first time.

1. **Add Test Users** (Quick Fix):

    - Go to [Google Cloud Console](https://console.cloud.google.com/)
    - Navigate to **APIs & Services** > **OAuth consent screen**
    - Scroll to **Test users** section
    - Click **Add Users**
    - Add your email address: `amirul96shafiq.harun@gmail.com`
    - Click **Save**
    - Try connecting again

2. **Complete OAuth Consent Screen**:

    - Fill in all required fields in OAuth consent screen
    - App name: "CheQQme Data Center"
    - User support email: Your email
    - Developer contact: Your email
    - Save and continue through all steps

3. **Verify User Type**:
    - Ensure you selected "External" user type
    - For Google Workspace domains, you can use "Internal"

### Error: "Google Calendar Access Required"

**Solution:** Connect your Google Calendar account from the Profile page.

### Error: "Failed to generate Google Meet link"

**Possible causes:**

1. Access token expired - Reconnect Google Calendar
2. Calendar API not enabled - Enable it in Google Cloud Console
3. Invalid OAuth credentials - Verify `.env` configuration

### Error: "Authentication failed"

**Solution:**

1. **SSL Certificate Issue** (Most common in local development):

    - Error: `cURL error 60: SSL certificate problem`
    - **Fix**: The GoogleMeetService now automatically disables SSL verification in local environment
    - If you still see this error, check your `.env` has `APP_ENV=local`

2. **Check redirect URI mismatch**:

    - Ensure Google Cloud Console has: `http://localhost:8000/auth/google/calendar/callback`
    - The service uses the calendar-specific redirect URI, not the general OAuth one

3. **Verify Google Cloud Console configuration**:

    - Check that redirect URIs in Google Cloud Console match your `.env` configuration
    - Ensure Calendar API is enabled
    - Verify OAuth client credentials are correct

4. **Check OAuth consent screen**:
    - Make sure you've added yourself as a test user
    - Verify the app is in "Testing" mode

### Token Refresh Issues

The service automatically refreshes expired tokens. If this fails:

1. Disconnect and reconnect Google Calendar
2. Check error logs: `php artisan pail`
3. Verify refresh token is stored correctly

## Security Considerations

1. **Token Storage**:

    - Tokens are stored encrypted in the database (JSON format)
    - Only the user can access their own tokens

2. **Scopes**:

    - Only requests Google Calendar Events scope
    - Minimal permissions required

3. **HTTPS**:

    - Always use HTTPS in production
    - Update redirect URIs accordingly

4. **Token Revocation**:
    - Users can disconnect at any time
    - Tokens are removed from database on disconnect
    - Users should also revoke access from [Google Account settings](https://myaccount.google.com/permissions)

## Production Deployment Checklist

-   [ ] Enable Google Calendar API in production project
-   [ ] Create production OAuth client ID
-   [ ] Update `.env` with production credentials
-   [ ] Update redirect URIs to production URLs
-   [ ] Enable HTTPS for all OAuth flows
-   [ ] Test OAuth flow in production
-   [ ] Test meeting link generation
-   [ ] Monitor error logs for OAuth issues

## Support

For issues or questions:

1. Check application logs: `php artisan pail`
2. Review Google Cloud Console logs
3. Verify OAuth configuration
4. Check network connectivity to Google APIs

## References

-   [Google Calendar API Documentation](https://developers.google.com/calendar/api/v3/reference)
-   [Google OAuth 2.0 Documentation](https://developers.google.com/identity/protocols/oauth2)
-   [Google Meet Developer Documentation](https://developers.google.com/meet)
-   [Laravel Socialite Documentation](https://laravel.com/docs/socialite)
