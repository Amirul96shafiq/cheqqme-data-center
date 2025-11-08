<?php

return [
    // Navigation
    'navigation_group' => 'Users',
    'navigation_label' => 'Users',

    // Labels
    'labels' => [
        'singular' => 'User',
        'plural' => 'Users',
        'edit-user' => 'Edit User',
    ],

    // Actions
    'actions' => [
        'create' => 'Create User',
        'delete' => 'Delete User',
    ],

    // Sections
    'section' => [
        'profile_settings' => 'Profile Settings',
        'profile_settings_description' => 'Configure and customize your profile settings here',
        'connection_settings' => 'Connection Settings',
        'connection_settings_description' => 'Manage your account connections and integrations',
        'spotify_integration' => 'Spotify Integration',
        'spotify_integration_description' => 'Display your currently playing music from Spotify',
        'password_settings' => 'Password Settings',
        'user_info' => 'User Information',
        'password_info' => 'Password Information',
        'password_info_description' => 'Enable Change Password? toggle to view this field',
        'password_info_description_profile' => 'Change and update your account password here',
        'danger_zone' => 'Danger Zone',
        'danger_zone_description' => 'Enable User Deletion? toggle to view this field',
        'activity_logs' => 'Activity Logs',
    ],

    // Form Fields
    'form' => [
        'avatar' => 'Avatar',
        'cover_image' => 'Cover Image',
        'cover_image_helper' => 'Upload a cover image for your profile (recommended: max 20MB)',
        'web_app_background' => 'Stylized Background',
        'web_app_background_helper' => 'Enable custom background images for CheQQme Data Center',
        'background_preview' => 'Background Preview',
        'enabled' => 'Enabled: Stylized Mode',
        'disabled' => 'Disabled: Focus Mode',
        'username' => 'Username',
        'name' => 'Name',
        'email' => 'Email',
        'phone_number' => 'Phone Number',
        'online_status' => 'Online Status',
        'online_status_helper' => 'Choose your online status that will be visible to other users',
        'change_password' => 'Change Password',
        'generate_password' => 'Generate Strong Password',
        'old_password' => 'Old Password',
        'new_password' => 'New Password',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'password_helper' => 'Must be at least 5 characters',
        'confirm_new_password' => 'Confirm New Password',
        'user_deletion' => 'User Deletion',
        'user_confirm_title' => 'User Deletion Confirmation',
        'user_confirm_placeholder' => 'Type exactly CONFIRM DELETE USER (case-sensitive) to enable delete button below',
        'user_confirm_helpertext' => 'CONFIRM DELETE USER',
        'name_helper' => 'Automatically filled with username if left empty, changeable',
        'personalize' => 'Personalize',
        'uploading' => 'Uploading...',

        // Google Connection
        'disconnect_google' => 'Disconnect',
        'disconnect_google_confirm' => 'Disconnect Google Account',
        'disconnect_google_description' => 'Are you sure you want to disconnect your Google account? You will no longer be able to sign in using Google, but you can still use your username and password.',
        'disconnect' => 'Disconnect',
        'cancel' => 'Cancel',
        'google_disconnected' => 'Google Account Disconnected',
        'google_disconnected_body' => 'Your Google account has been successfully disconnected. You can still sign in using your username and password.',
        'google_connection' => 'Google oAuth',
        'google_description' => 'Connect your Google account for faster login and synchronize your Google avatar with your profile.',
        'connect_google' => 'Connect Now',
        'google_connected' => 'Google Account Connected',
        'google_connected_body' => 'Your Google account has been successfully connected. You can now sign in using Google.',
        'google_connection_failed' => 'Google Connection Failed',
        'connection_status' => 'Connection Status',
        'connected' => 'Connected',
        'not_connected' => 'Not Connected',

        // Microsoft Connection
        'connect_microsoft' => 'Coming Soon',
        'microsoft_coming_soon' => 'Coming Soon',

        // Spotify Connection
        'connect_spotify' => 'Connect Now',
        'disconnect_spotify' => 'Disconnect',
        'disconnect_spotify_confirm' => 'Disconnect Spotify Account',
        'disconnect_spotify_description' => 'Are you sure you want to disconnect your Spotify account? You will no longer be able to use Spotify integration features.',
        'spotify_disconnected' => 'Spotify Account Disconnected',
        'spotify_disconnected_body' => 'Your Spotify account has been successfully disconnected.',
        'spotify_connection' => 'Spotify Player',
        'spotify_description' => 'Connect your Spotify account to access music integration features and synchronize your Spotify profile.',
        'spotify_connected' => 'Spotify Account Connected',
        'spotify_connected_body' => 'Your Spotify account has been successfully connected. You can now use Spotify integration features.',
        'spotify_connection_failed' => 'Spotify Connection Failed',
    ],

    // Online Status Indicator
    'indicator' => [
        // Online Status Options
        'online_status_online' => 'Online',
        'online_status_away' => 'Away',
        'online_status_dnd' => 'Do Not Disturb',
        'online_status_invisible' => 'Invisible',

        // Online Status Messages
        'online_status_updated' => 'Status updated successfully.',
        'online_status_update_failed' => 'Failed to update status',
    ],

    // User Profile Badges
    'badge' => [
        'country' => 'Country',
        'timezone' => 'Timezone',
        'google_oauth' => 'Google OAuth Connected',
        'google_calendar' => 'Google Calendar API Connected',
        'zoom_api' => 'Zoom API Connected',
        'spotify' => 'Spotify Connected',
    ],

    // Table Columns
    'table' => [
        'id' => 'ID',
        'avatar' => 'Avatar',
        'username' => 'Username',
        'email' => 'Email',
        'phone_number' => 'Phone Number',
        'timezone' => 'Timezone',
        'country' => 'Country',
        'created_at' => 'Created At',
        // 'updated_at_by' => 'Updated At (by)',
        'personalize' => 'Personalize',
        'settings' => 'Settings',
        'chatbot-history' => 'Chatbot History',
    ],

    // Filters
    'filter' => [
        'country_code' => 'Country Code',
        'has_cover_image' => 'Has Cover Image',
        'timezone' => 'Timezone',
        'trashed' => 'Trashed',
    ],

    // Modal
    'modal' => [
        'create_heading' => 'Create New User',
    ],

    // Notifications
    'notifications' => [
        'saved' => 'Saved',
        'saved_body' => 'Refresh the page to view the changes.',
        'saved_password' => 'Saved',
        'saved_password_body' => 'Refresh the page to view the changes.',
        'validation_error' => 'Validation Error',
        'old_password_incorrect' => 'The old password is incorrect.',
        'created' => 'User Created',
        'created_body' => 'User :name has been successfully created.',
        'create_failed' => 'User Creation Failed',
        'password_generated' => 'Password Generated',
        'password_generated_body' => 'Generated password: :password (Copy this password before closing)',
    ],
];
