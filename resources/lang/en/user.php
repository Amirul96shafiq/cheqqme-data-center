<?php

return [
    'navigation_group' => 'User Management',
    'navigation_label' => 'Users',

    'labels' => [
        'singular' => 'User',
        'plural' => 'Users',
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
        'password_settings' => 'Password Settings',
        'user_info' => 'User Information',
        'password_info' => 'Password Information',
        'password_info_description' => 'Enable Change Password? toggle to view this field',
        'password_info_description_profile' => 'Leave blank if you don\'t want to change your password',
        'danger_zone' => 'Danger Zone',
        'danger_zone_description' => 'Enable User Deletion? toggle to view this field',
    ],

    'form' => [
        'saved' => 'Saved.',
        'saved_body' => 'Please re-login or refresh the page.',
        'saved_password' => 'Saved.',
        'saved_password_body' => 'Please re-login or refresh the page.',
        'avatar' => 'Avatar',
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
    ],

    'table' => [
        'id' => 'ID',
        'username' => 'Username',
        'name' => 'Name',
        'email' => 'Email',
        'created_at' => 'Created At',
        'updated_at_by' => 'Updated At (by)',
    ],
];
