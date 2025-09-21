<?php

return [
    'navigation_group' => 'Resources',
    'navigation_label' => 'Clients',

    'labels' => [
        'singular' => 'Client',
        'plural' => 'Clients',
        'edit-client' => 'Edit Client',
    ],

    'navigation' => [
        'labels' => 'Clients',
    ],

    'actions' => [
        'create' => 'Create Client',
    ],

    'section' => [
        'client_info' => 'Client Information',
        'company_info' => 'Client Company Information',
        'company_projects' => 'Client Projects',
        'extra_info' => 'Client Additional Information',
        'important_urls' => 'Client Important URLs',
        'activity_logs' => 'Activity Logs',
    ],

    'form' => [
        'pic_name' => 'PIC Name',
        'pic_email' => 'PIC Email',
        'pic_contact_number' => 'PIC Contact Number',
        'company_name' => 'Company Name',
        'company_email' => 'Company Email',
        'company_address' => 'Company Address',
        'billing_address' => 'Billing Address',
        'notes' => 'Notes',
        'notes_helper' => 'Remaining characters: :count',
        'notes_warning' => 'Notes must not exceed 500 visible characters.',
        'company_name_helper' => 'Defaults to the name of the person in charge, Changeable.',
        'extra_information' => 'Extra Information',
        'extra_title' => 'Title',
        'extra_value' => 'Value',
        'add_extra_info' => '+ Add Extra Information',
        'title_placeholder_short' => 'Title goes here',
    ],

    'table' => [
        'id' => 'ID',
        'pic_name' => 'Client Name (Company)',
        'pic_contact_number' => 'Contact Number',
        'project_count' => 'Projects',
        'important_url_count' => 'Important URLs',
        'created_at' => 'Created At',
        'updated_at_by' => 'Updated At (by)',
        'tooltip' => [
            'full_name' => 'Full Name',
            'company' => 'Company',
        ],
    ],

    'search' => [
        'pic_email' => 'PIC Email',
        'pic_contact_number' => 'PIC Phone No.',
        'company_name' => 'Company Name',
    ],

    'filter' => [
        'trashed' => 'Trashed',
    ],
];
