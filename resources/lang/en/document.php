<?php

return [
    'navigation_group' => 'Resources',
    'navigation_label' => 'Documents',

    'labels' => [
        'singular' => 'Document',
        'plural' => 'Documents',
        'edit-document' => 'Edit Document',
    ],

    'navigation' => [
        'labels' => 'Documents',
    ],

    'actions' => [
        'create' => 'Create Document',
    ],

    'section' => [
        'document_info' => 'Document Information',
        'extra_info' => 'Document Extra Information',
        'activity_logs' => 'Activity Logs',
    ],

    'form' => [
        'document_title' => 'Document Title',
        'project' => 'Project',
        'client' => 'Client',
        'document_type' => 'Document Type',
        'external' => 'External',
        'internal' => 'Internal',
        'document_url' => 'Document URL',
        'document_url_note' => 'URL for external documents',
        'open_url' => 'Open URL',
        'document_url_helper' => 'Open URL in a new tab',
        'document_upload' => 'Upload Document',
        'document_upload_helper' => 'Upload internal documents here (PDF, JPEG, PNG, DOC, DOCX, XLS, XLSX, CSV, PPT, PPTX) - max 20MB',
        'notes' => 'Notes',
        'notes_helper' => 'Remaining characters: :count',
        'notes_warning' => 'Notes must not exceed 500 visible characters.',
        'extra_information' => 'Extra Information',
        'extra_title' => 'Title',
        'extra_value' => 'Value',
        'add_extra_info' => '+ Add Extra Information',
        'title_placeholder_short' => 'Title goes here',
        'create_project' => 'Create Project',
    ],

    'table' => [
        'id' => 'ID',
        'title' => 'Title',
        'type' => 'Type',
        'file_type' => 'File Type',
        'project' => 'Project',
        'created_at_by' => 'Created At (By)',
        'updated_at_by' => 'Updated At (by)',
        'internal' => 'Internal',
        'external' => 'External',
        'client' => 'Client',
        'document_url' => 'Document URL',
    ],

    'tabs' => [
        'all' => 'All',
        'today' => 'Today',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'this_year' => 'This Year',
    ],

    'search' => [
        'project' => 'Project',
        'type' => 'Type',
        'url' => 'URL',
        'file_path' => 'File Path',
    ],

    'filter' => [
        'trashed' => 'Trashed',
    ],

    'drag_drop' => [
        'large_file_title' => 'More than 5MB detected, please upload manually',
        'large_file_message' => 'Large file detected (:sizeMB). Please use the file upload field below to upload ":filename".',
        'file_too_large' => 'File size exceeds 20MB limit. Your file is :sizeMBMB.',
        'unsupported_file_type' => 'Unsupported file type. Please upload PDF, Word, Excel, PowerPoint, images, videos, or CSV files.',
        'drop_file_to_upload_document' => 'Drop a file to upload document',
        'drop_file_to_upload_document_helper' => 'Automatically upload below 5MB files. If it\'s larger than 5MB, you can upload the file manually after redirecting to the Create Document page.',
        'filament_upload_detected' => 'Filament upload fields detected, using native drag-drop instead',
    ],

    'tooltip' => [
        'external_url' => 'External URL: :url',
        'internal_file' => 'Internal file: :path',
        'no_url' => 'No external URL provided',
        'no_file' => 'No internal file uploaded',
        'unknown_type' => 'Unknown document type',
    ],
];
