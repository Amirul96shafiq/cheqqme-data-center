<?php

return [
    'navigation_group' => 'Data Management',
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
    ],

    'table' => [
        'id' => 'ID',
        'title' => 'Title',
        'type' => 'Type',
        'project' => 'Project',
        'created_at' => 'Created At',
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
        'drop_file_to_upload_document_helper' => 'Automatically upload below 5MB files. If it\'s larger than 5MB, upload the file manually.',
        'filament_upload_detected' => 'Filament upload fields detected, using native drag-drop instead',
    ],
];
