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
    'user_info' => 'User Information',
    'password_info' => 'Password Information',
    'password_info_description' => 'Enable Change Password? toggle to view this field',
    'danger_zone' => 'Danger Zone',
    'danger_zone_description' => 'Enable User Deletion? toggle to view this field',
  ],

  'form' => [
    'username' => 'Username',
    'name' => 'Name',
    'email'=> 'Email',
    'change_password' => 'Change Password',
    'generate_password' => 'Generate Strong Password',
    'old_password' => 'Old Password',
    'new_password' => 'New Password',
    'password_helper' => 'Must be at least 5 characters',
    'confirm_new_password' => 'Confirm New Password',
    'user_deletion' => 'User Deletion',
    'user_confirm_title' => 'User Deletion Confirmation',
    'user_confirm_placeholder' => 'Type exactly CONFIRM DELETE USER (case-sensitive) to enable delete button below',
    'user_confirm_helpertext' => 'CONFIRM DELETE USER',
  ],

  'table' => [
    'username' => 'Username',
    'name' => 'Name',
    'email' => 'Email',
    'created_at' => 'Created At',
    'updated_at_by' => 'Updated At (by)',
  ],
];