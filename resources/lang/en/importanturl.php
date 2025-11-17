<?php

return [
    'navigation_group' => 'Resources',
    'navigation_label' => 'Important URLs',

    'labels' => [
        'singular' => 'Important URL',
        'plural' => 'Important URLs',
        'edit-important-url' => 'Edit Important URL',
    ],

    'navigation' => [
        'labels' => 'Important URLs',
    ],

    'actions' => [
        'create' => 'Create Important URL',
        'make_draft' => 'Change to Draft',
        'make_active' => 'Change to Active',
        'make_draft_tooltip' => 'Change this important URL to Draft (only visible to creator)',
        'make_active_tooltip' => 'Change this important URL to Active (visible to all users)',
        'visibility_status_updated' => 'Visibility Status Updated',
        'important_url_activated' => 'Important URL is now Active and visible to all users.',
        'important_url_made_draft' => 'Important URL is now Draft and only visible to you.',
    ],

    'section' => [
        'important_url_info' => 'Important URL Information',
        'extra_info' => 'Important URL Extra Information',
        'activity_logs' => 'Activity Logs',
        'visibility_status' => 'Visibility Resource Information',
    ],

    // Form fields
    'form' => [
        'important_url_title' => 'Important URL Title',
        'project' => 'Project',
        'client' => 'Client',
        'important_url' => 'Important URL',
        'important_url_note' => 'URL for important links',
        'open_url' => 'Open URL',
        'important_url_helper' => 'Open URL in a new tab',
        'notes' => 'Notes',
        'notes_helper' => 'Remaining characters: :count',
        'notes_warning' => 'Notes must not exceed 500 visible characters.',
        'extra_information' => 'Extra Information',
        'extra_title' => 'Title',
        'extra_value' => 'Value',
        'add_extra_info' => '+ Add Extra Information',
        'title_placeholder_short' => 'Title goes here',
        'create_project' => 'Create Project',
        'create_client' => 'Create Client',
        'visibility_status' => 'Visibility Status',
        'visibility_status_active' => 'Active',
        'visibility_status_draft' => 'Draft',
        'visibility_status_helper' => 'Active Important URLs are visible to all users. Draft Important URLs are only visible to their creator.',
    ],

    // Table columns
    'table' => [
        'id' => 'ID',
        'title' => 'Title',
        'link' => 'Link',
        'project' => 'Project',
        'client' => 'Client',
        'important_url' => 'URL Link',
        'visibility_status' => 'Visibility',
        'visibility_status_active' => 'Active',
        'visibility_status_draft' => 'Draft',
        'created_at_by' => 'Created At (By)',
        'updated_at_by' => 'Updated At (by)',
        'tooltip' => [
            'full_name' => 'Full Name',
            'company' => 'Company',
        ],
    ],

    // Filters
    'filters' => [
        'client_id' => 'Client',
        'project_id' => 'Project',
    ],

    'tabs' => [
        'all' => 'All',
        'today' => 'Today',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'this_year' => 'This Year',
    ],
    
    // Search
    'search' => [
        'project' => 'Project',
        'client' => 'Client',
        'url' => 'URL',
    ],
    
    'filter' => [
        'trashed' => 'Trashed Records',
    ],
];
