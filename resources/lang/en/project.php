<?php

return [
    'navigation_group' => 'Data Management',
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
    ],

    'section' => [
        'project_info' => 'Project Information',
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
    ],

    'table' => [
        'id' => 'ID',
        'title' => 'Title',
        'description' => 'Description',
        'client' => 'Client',
        'status' => 'Status',
        'document_count' => 'Documents',
        'planning' => 'Planning',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'total_documents' => 'Total Documents',
        'created_at' => 'Created At',
        'updated_at_by' => 'Updated At (by)',
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
