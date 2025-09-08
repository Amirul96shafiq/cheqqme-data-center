<?php

return [
    'navigation_group' => 'User Management',
    'navigation_label' => 'Users',

    'labels' => [
        'singular' => 'User',
        'plural' => 'Users',
        'edit-user' => 'Edit User',
    ],

    'navigation' => [
        'labels' => 'Users',
    ],

    'actions' => [
        'create' => 'Create User',
        'delete' => 'Delete User',
    ],

    'section' => [
        'profile_settings' => 'Profile Settings',
        'profile_settings_description' => 'Configure and customize your profile settings here',
        'google_connection_settings' => 'Google Connection Settings',
        'google_connection_settings_description' => 'Manage your Google account connections and integrations',
        'password_settings' => 'Password Settings',
        'user_info' => 'User Information',
        'password_info' => 'Password Information',
        'password_info_description' => 'Enable Change Password? toggle to view this field',
        'password_info_description_profile' => 'Leave blank if you don\'t want to change your password',
        'danger_zone' => 'Danger Zone',
        'danger_zone_description' => 'Enable User Deletion? toggle to view this field',
        'activity_logs' => 'Activity Logs',
    ],

    'form' => [
        'saved' => 'Saved.',
        'saved_body' => 'Please re-login or refresh the page.',
        'saved_password' => 'Saved.',
        'saved_password_body' => 'Please re-login or refresh the page.',
        'avatar' => 'Avatar',
        'cover_image' => 'Cover Image',
        'cover_image_helper' => 'Upload a cover image for your profile (recommended: max 20MB)',
        'username' => 'Username',
        'name' => 'Name',
        'email' => 'Email',
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
        'disconnect_google' => 'Disconnect',
        'disconnect_google_confirm' => 'Disconnect Google Account',
        'disconnect_google_description' => 'Are you sure you want to disconnect your Google account? You will no longer be able to sign in using Google, but you can still use your username and password.',
        'disconnect' => 'Disconnect',
        'cancel' => 'Cancel',
        'google_disconnected' => 'Google Account Disconnected',
        'google_disconnected_body' => 'Your Google account has been successfully disconnected. You can still sign in using your username and password.',
        'google_connection' => 'Google Connection',
        'google_description' => 'To use as an alternative login method & synchronize your Google avatar with your profile.',
        'connect_google' => 'Connect Now',
        'google_connected' => 'Google Account Connected',
        'google_connected_body' => 'Your Google account has been successfully connected. You can now sign in using Google.',
    ],

    'table' => [
        'id' => 'ID',
        'avatar' => 'Avatar',
        'username' => 'Username',
        'email' => 'Email',
        'created_at' => 'Created At',
        'updated_at_by' => 'Updated At (by)',
        'personalize' => 'Personalize',
        'settings' => 'Settings',
    ],

    'filter' => [
        'has_cover_image' => 'Has Cover Image',
        'timezone' => 'Timezone',
        'trashed' => 'Trashed',
    ],
];
