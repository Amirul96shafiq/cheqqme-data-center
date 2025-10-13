# Fix Google OAuth Verification Error

## The Problem

You're seeing this error when trying to connect Google Calendar:

> **Access blocked: CheQQme Data Center has not completed the Google verification process**
>
> Error 403: access_denied

## Quick Fix (5 minutes)

### Step 1: Go to Google Cloud Console

1. Open [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project (the one you created for CheQQme Data Center)

### Step 2: Add Yourself as Test User

1. Navigate to **APIs & Services** > **OAuth consent screen**
2. Scroll down to the **Test users** section
3. Click **Add Users**
4. Enter your email: `amirul96shafiq.harun@gmail.com`
5. Click **Save**

### Step 3: Try Again

1. Go back to your CheQQme Data Center app
2. Navigate to Profile page
3. Click **Connect Google Calendar**
4. It should work now!

## If That Doesn't Work

### Complete OAuth Consent Screen Setup

1. **In OAuth consent screen**, fill in these required fields:

    - **App name**: `CheQQme Data Center`
    - **User support email**: Your email address
    - **Developer contact information**: Your email address

2. **Continue through all tabs**:
    - **Scopes**: Make sure Calendar scope is added
    - **Test users**: Add your email
    - **Summary**: Review and save

### Verify User Type

-   Make sure you selected **"External"** as the user type
-   If you have Google Workspace, you can use **"Internal"**

## Why This Happens

Google requires all OAuth applications to go through a verification process for security. During development/testing, you can add specific email addresses as "test users" to bypass this requirement.

## For Production Later

When you're ready to deploy to production and allow any user to connect:

1. **Submit for verification** in Google Cloud Console
2. **Provide required information**:
    - Privacy policy URL
    - Terms of service URL
    - App screenshots
    - Verification can take several days

## Alternative: Use Service Account (Advanced)

If you want to avoid OAuth entirely, you can use a Google Service Account, but this is more complex and less secure for user data.

## Need Help?

If you're still having issues:

1. Check that Google Calendar API is enabled in your project
2. Verify your OAuth client credentials in `.env`
3. Make sure redirect URIs match exactly
4. Check the application logs: `php artisan pail`

---

**Quick Summary**: Add your email as a test user in Google Cloud Console → OAuth consent screen → Test users → Add Users → Save → Try again!
