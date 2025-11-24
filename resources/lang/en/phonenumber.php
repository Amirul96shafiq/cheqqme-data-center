<?php

return [
    'navigation_group' => 'Resources',
    'navigation_label' => 'Phone Numbers',

    'labels' => [
        'singular' => 'Phone Number',
        'plural' => 'Phone Numbers',
        'edit-phone-number' => 'Edit Phone Number',
    ],

    'navigation' => [
        'labels' => 'Phone Numbers',
    ],

    'actions' => [
        'create' => 'Create Phone Number',
        'make_draft' => 'Change to Draft',
        'make_active' => 'Change to Active',
        'make_draft_tooltip' => 'Change this phone number to Draft (Only visible to creator)',
        'make_active_tooltip' => 'Change this phone number to Active (Visible to all users)',
        'visibility_status_updated' => 'Visibility Status Updated',
        'phone_number_activated' => 'Phone number is now active and visible to all users.',
        'phone_number_made_draft' => 'Phone number is now draft and only visible to you.',
    ],

    'section' => [
        'phone_number_info' => 'Phone Number Information',
        'extra_info' => 'Phone Number Additional Information',
        'activity_logs' => 'Activity Logs',
        'visibility_status' => 'Resource Visibility Information',
    ],

    'form' => [
        'phone_number_title' => 'Phone Number Title',
        'phone_number' => 'Phone Number',
        'notes' => 'Notes',
        'notes_helper' => 'Remaining characters: :count',
        'notes_warning' => 'Notes must not exceed 500 visible characters.',
        'extra_information' => 'Extra Information',
        'extra_title' => 'Title',
        'extra_value' => 'Value',
        'add_extra_info' => '+ Add Extra Information',
        'title_placeholder_short' => 'Title goes here',
        'visibility_status' => 'Visibility Status',
        'visibility_status_active' => 'Active',
        'visibility_status_draft' => 'Draft',
        'visibility_status_helper' => 'Active Phone Numbers are visible to all users. Draft Phone Numbers are only visible to their creator.',
        'visibility_status_helper_readonly' => 'Only the user who created this resource can change the visibility status. Please contact',
    ],

    'table' => [
        'id' => 'ID',
        'title' => 'Title',
        'country' => 'Country',
        'phone_number' => 'Phone Number',
        'visibility_status' => 'Visibility',
        'visibility_status_active' => 'Active',
        'visibility_status_draft' => 'Draft',
        'created_at_by' => 'Created At (By)',
        'updated_at_by' => 'Updated At (by)',
    ],

    'search' => [
        'phone' => 'Phone No.',
    ],

    'filter' => [
        'trashed' => 'Trashed',
        'country_code' => 'Country Code',
    ],
];
