<?php

return [
    'navigation_group' => 'Resources',
    'navigation_label' => 'Projects',

    'labels' => [
        'singular' => 'Project',
        'plural' => 'Projects',
        'edit-project' => 'Edit Project',
    ],

    'navigation' => [
        'labels' => 'Projects',
    ],

    'actions' => [
        'create' => 'Create Project',
        'share_issue_tracker_link' => 'Share Issue Tracker Link',
        'share_issue_tracker_link_description' => 'Preview and share issue tracker link details',
        'issue_tracker_preview' => 'Issue Tracker Link Preview',
        'copy_to_clipboard' => 'Copy to Clipboard',
        'edit_project' => 'Edit Project',
    ],

    'section' => [
        'project_info' => 'Project Information',
        'issue_tracker_info' => 'Issue Tracker Information',
        'extra_info' => 'Project Extra Information',
        'project_documents' => 'Project Documents',
        'important_urls' => 'Project Important URLs',
        'activity_logs' => 'Activity Logs',
    ],

    'form' => [
        'project_title' => 'Project Title',
        'client' => 'Client',
        'project_url' => 'Project URL',
        'project_description' => 'Project Description',
        'project_status' => 'Project Status',
        'planning' => 'Planning',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'notes' => 'Notes',
        'notes_helper' => 'Remaining characters: :count',
        'notes_warning' => 'Notes must not exceed 500 visible characters.',
        'extra_information' => 'Extra Information',
        'extra_title' => 'Title',
        'extra_value' => 'Value',
        'add_extra_info' => '+ Add Extra Information',
        'title_placeholder_short' => 'Title goes here',
        'create_client' => 'Create Client',
        'issue_tracker_code' => 'Issue Tracker Code',
        'issue_tracker_url' => 'Issue Tracker URL',
        'issue_tracker_code_helper' => 'Issue Tracker URL: :url',
        'issue_tracker_code_helper_new' => 'Issue Tracker Code will be auto-generated when you save this project.',
        'open_issue_tracker' => 'Open Issue Tracker',
        'tracking_tokens' => 'Tracking Tokens',
        'tracking_token' => 'Tracking Token',
        'task_status' => 'Status',
        'edit_task' => 'Edit Task',
        'view_status' => 'Status Page',
        'open_edit_task' => 'Open Edit Task',
        'open_status_page' => 'Open Status Page',
    ],

    'table' => [
        'id' => 'ID',
        'title' => 'Title',
        'description' => 'Description',
        'client' => 'Client',
        'issue_tracker_code' => 'Issue Tracker Code',
        'status' => 'Status',
        'document_count' => 'Documents',
        'important_url_count' => 'Important URLs',
        'planning' => 'Planning',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'total_documents' => 'Total Documents',
        'created_at' => 'Created At',
        'updated_at_by' => 'Updated At (by)',
        'tooltip' => [
            'full_name' => 'Full Name',
            'company' => 'Company',
        ],
        'copy_link_tooltip' => 'Click to copy issue tracker link',
        'copy_success' => 'Issue tracker link copied to clipboard!',
        'copy_failed' => 'Failed to copy link',
        'copied' => 'Copied!',
    ],

    'filter' => [
        'status' => 'Status',
        'planning' => 'Planning',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'trashed' => 'Trashed',
    ],

    'search' => [
        'title' => 'Project Title',
        'client' => 'Client',
        'status' => 'Status',
    ],
];
