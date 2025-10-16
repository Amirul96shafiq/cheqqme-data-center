# Zoom Meeting Integration Setup Guide

This guide explains how to set up and use the Zoom Meeting integration in the CheQQme Data Center application.

## Overview

The Zoom Meeting integration allows users to:

-   Connect their Zoom account via OAuth
-   Generate Zoom meeting links directly from the application
-   Automatically create scheduled Zoom meetings
-   Manage meeting links associated with clients, projects, and documents

## Prerequisites

1. Zoom account (free or paid)
2. Zoom App Marketplace account
3. Laravel application with GuzzleHTTP installed

## Step 1: Create Zoom App

1. Go to [Zoom App Marketplace](https://marketplace.zoom.us/develop/create)
2. Click **Develop** > **Build App**
3. Choose **OAuth** as the app type
4. Click **Create**
5. Enter app information:
    - **App Name**: CheQQme Data Center Zoom Integration
    - **Choose app type**: User-managed app
    - **Would you like to publish this app on Zoom Marketplace**: No (unless you want public distribution)

## Step 2: Configure OAuth Credentials

### App Credentials

1. Navigate to the **App Credentials** tab
2. Copy the **Client ID** and **Client Secret** (you'll need these for `.env`)
3. Under **Redirect URL for OAuth**, add:
    - Development: `http://localhost:8000/auth/zoom/callback`
    - Production: `https://yourdomain.com/auth/zoom/callback`
4. Under **OAuth allow list**, add:
    - Development: `http://localhost:8000`
    - Production: `https://yourdomain.com`

### Scopes

1. Navigate to the **Scopes** tab
2. Add the following scopes:
    - `meeting:write` - Create meetings
    - `meeting:read` - Read meeting details
    - `meeting:update` - Update meetings
    - `meeting:delete` - Delete meetings
    - `user:read` - Read user profile

### Information

1. Fill in required information:
    - **Short Description**: Integration for CheQQme Data Center
    - **Long Description**: Allows users to create and manage Zoom meetings from the CheQQme Data Center application
    - **Developer Contact Information**: Your email
    - **Company Name**: Your company name

## Step 3: Configure Environment Variables

Update your `.env` file with the Zoom OAuth credentials:

```env
###############################################
# Zoom OAuth Configuration
###############################################
ZOOM_CLIENT_ID=your_zoom_client_id_here
ZOOM_CLIENT_SECRET=your_zoom_client_secret_here
ZOOM_REDIRECT_URI=http://localhost:8000/auth/zoom/callback
```

**Note:** For production, update the redirect URI to match your production domain.

## Step 4: Run Database Migrations

Run the migration to add Zoom fields to the users table:

```bash
php artisan migrate
```

Look for the migration:

-   `2025_10_16_231350_add_zoom_fields_to_users_table.php`

## Step 5: Connect Zoom Account

### For Users:

1. Log in to the application
2. Navigate to **Profile** page
3. Scroll to the **External Services** section
4. Find the **Zoom Connection** fieldset
5. Click **Connect Zoom**
6. You'll be redirected to Zoom's OAuth consent screen
7. Click **Authorize** to grant the required permissions
8. After authorization, you'll be redirected back to your profile

**Status indicators:**

-   🟢 **Connected**: Shows connection date
-   🔴 **Not Connected**: Ready to connect

### Disconnect Zoom:

1. Go to **Profile** > **External Services**
2. Click **Disconnect Zoom**
3. Confirm the action

**Note:** Disconnecting will prevent you from generating new Zoom meeting links, but existing links will remain functional.

## Step 6: Create Meeting Links

### Using the Meeting Links Resource:

1. Navigate to **Resources** > **Meeting Links**
2. Click **Create**
3. Fill in the meeting details:

    - **Meeting Title**: Enter a descriptive title
    - **Platform**: Select "Zoom"
    - Click **Generate Zoom Meeting URL**

4. If not connected to Zoom:

    - You'll see a notification to connect
    - Click the notification action to connect
    - Return and try generating again

5. Once generated:
    - The meeting URL will appear in the form
    - You can **Copy** the link
    - You can **Regenerate** if needed
    - Click **Save** to store the meeting link

### Meeting Link Features:

-   **Regenerate**: Create a new meeting link (updates the existing one)
-   **Delete Link**: Remove the meeting link from Zoom
-   **Copy to Clipboard**: Quickly copy the meeting URL
-   **Associate Resources**: Link clients, projects, documents, and users

### Additional Information Tab:

-   Add meeting description/notes
-   Add custom extra information fields
-   Invite users to the meeting

## How It Works

### Architecture:

1. **ZoomMeetingService** (`app/Services/ZoomMeetingService.php`):

    - Manages Zoom API interactions
    - Handles OAuth token management
    - Creates, updates, and deletes meetings

2. **ZoomController** (`app/Http/Controllers/Auth/ZoomController.php`):

    - Handles OAuth authentication flow
    - Stores Zoom tokens in user records
    - Manages connection/disconnection

3. **MeetingLinkResource** (`app/Filament/Resources/MeetingLinkResource.php`):

    - Filament resource for managing meeting links
    - Integrates with ZoomMeetingService
    - Provides UI for generating and managing links

4. **Database**:
    - `users` table: Stores `zoom_token` and `zoom_connected_at`
    - `meeting_links` table: Stores meeting information and associations

### OAuth Flow:

```
User clicks "Connect Zoom"
    ↓
Redirect to Zoom OAuth (with required scopes)
    ↓
User authorizes the app
    ↓
Zoom redirects back with authorization code
    ↓
Exchange code for access/refresh tokens
    ↓
Store tokens in user record
    ↓
User can now generate Zoom meeting links
```

### Meeting Link Generation:

```
User clicks "Generate Zoom Meeting URL"
    ↓
Check if user has valid Zoom token
    ↓
Use ZoomMeetingService to create meeting
    ↓
Zoom API returns meeting with join URL
    ↓
Store meeting URL and ID in form
    ↓
User saves the meeting link record
```

## API Reference

### ZoomMeetingService Methods:

#### `setAccessToken(string|array $accessToken): void`

Set the access token for the authenticated user.

#### `generateMeetingLink(string $title, ?string $startTime = null, ?int $duration = 60): ?array`

Generate a new Zoom meeting link.

**Returns:**

```php
[
    'meeting_url' => 'https://zoom.us/j/1234567890',
    'meeting_id' => '1234567890',
    'host_email' => 'user@example.com',
    'start_url' => 'https://zoom.us/s/1234567890',
    'password' => 'abc123'
]
```

#### `updateMeeting(string $meetingId, string $title, ?string $startTime = null, ?int $duration = 60): ?array`

Update an existing Zoom meeting.

#### `deleteMeeting(string $meetingId): bool`

Delete a Zoom meeting.

#### `hasValidAccess(): bool`

Check if the user has valid Zoom access.

## Troubleshooting

### Error: "Zoom Access Required"

**Solution:** Connect your Zoom account from the Profile page.

### Error: "Failed to generate Zoom meeting link"

**Possible causes:**

1. Access token expired - Reconnect Zoom account
2. Invalid OAuth credentials - Verify `.env` configuration
3. Missing scopes - Check app scopes in Zoom App Marketplace

### Error: "Authentication failed"

**Solution:**

1. **Check redirect URI mismatch**:

    - Ensure Zoom App Marketplace has: `http://localhost:8000/auth/zoom/callback`
    - Verify `.env` has matching `ZOOM_REDIRECT_URI`

2. **Verify Zoom App configuration**:

    - Check that redirect URIs match your `.env` configuration
    - Verify OAuth credentials are correct
    - Ensure app is activated

3. **SSL Certificate Issue** (Local development):
    - The ZoomMeetingService automatically disables SSL verification in local environment
    - If you still see SSL errors, check your `.env` has `APP_ENV=local`

### Token Refresh Issues

Zoom tokens expire after a certain period. The service can refresh them automatically:

1. If refresh fails, disconnect and reconnect Zoom
2. Check error logs: `php artisan pail`
3. Verify refresh token is stored correctly in database

### Meeting Not Created

**Check:**

-   User has valid Zoom access
-   Meeting title is provided
-   Start time is in the future (if provided)
-   User's Zoom account is active

## Security Considerations

1. **Token Storage**:

    - Tokens are stored in JSON format in the database
    - Only the user can access their own tokens
    - Consider encrypting tokens in production

2. **Scopes**:

    - Only requests necessary meeting scopes
    - Minimal permissions required

3. **HTTPS**:

    - Always use HTTPS in production
    - Update redirect URIs accordingly

4. **Token Revocation**:
    - Users can disconnect at any time
    - Tokens are removed from database on disconnect
    - Users should also revoke access from [Zoom App Marketplace](https://marketplace.zoom.us/user/installed)

## Production Deployment Checklist

-   [ ] Create Zoom App in Zoom App Marketplace
-   [ ] Add production redirect URI to app settings
-   [ ] Add all required scopes to app
-   [ ] Update `.env` with production credentials
-   [ ] Update redirect URI to production URL
-   [ ] Enable HTTPS for all OAuth flows
-   [ ] Test OAuth flow in production
-   [ ] Test meeting link generation
-   [ ] Monitor error logs for OAuth issues
-   [ ] Consider encrypting tokens at rest

## Support

For issues or questions:

1. Check application logs: `php artisan pail`
2. Review Zoom App Marketplace logs
3. Verify OAuth configuration
4. Check network connectivity to Zoom APIs

## References

-   [Zoom API Documentation](https://developers.zoom.us/docs/api/)
-   [Zoom OAuth Documentation](https://developers.zoom.us/docs/integrations/oauth/)
-   [Zoom Meeting API Reference](https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/meetingCreate)
-   [Laravel Documentation](https://laravel.com/docs)

## Comparison with Google Meet

| Feature             | Zoom                               | Google Meet             |
| ------------------- | ---------------------------------- | ----------------------- |
| OAuth Provider      | Zoom                               | Google                  |
| API Endpoint        | `https://api.zoom.us/v2/`          | Google Calendar API     |
| Meeting Creation    | Direct meeting creation            | Via calendar event      |
| Token Storage       | `zoom_token`                       | `google_calendar_token` |
| Password Protection | Available (included in response)   | Not applicable          |
| Host vs Participant | Separate URLs for host/participant | Same URL for all        |
| Recording           | Configurable via settings          | Google Workspace only   |

## Next Steps

After successful setup:

1. Test creating a meeting link
2. Verify the meeting appears in your Zoom account
3. Test joining the meeting
4. Configure any additional settings in the Zoom App
5. Train users on how to use the integration

---

_For detailed information about the CheQQme Data Center application, refer to the main README._
