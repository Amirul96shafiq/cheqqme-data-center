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
        'make_draft' => 'Change to Draft',
        'make_active' => 'Change to Active',
        'make_draft_tooltip' => 'Change this client to Draft status (only you can see it)',
        'make_active_tooltip' => 'Change this client to Active status (all users can see it)',
        'status_updated' => 'Status updated successfully',
        'client_activated' => 'Client is now Active and visible to all users.',
        'client_made_draft' => 'Client is now in Draft mode and only visible to you.',
    ],

    'section' => [
        'client_info' => 'Person In Charge Information',
        'staff_info' => 'Staff Information',
        'staff_info_description' => 'Manually add or Automatically add from Issue / Wishlist Tracker',
        'company_info' => 'Client Company Information',
        'company_projects' => 'Client Projects',
        'extra_info' => 'Client Additional Information',
        'status_info' => 'Visibility Resource Information',
        'important_urls' => 'Client Important URLs',
        'activity_logs' => 'Activity Logs',
    ],

    'form' => [
        'pic_name' => 'PIC Name',
        'pic_email' => 'PIC Email',
        'pic_contact_number' => 'PIC Contact Number',
        'staff_information' => 'Staff Information',
        'staff_name' => 'Staff Name',
        'staff_contact_number' => 'Staff Contact Number',
        'staff_email' => 'Staff Email',
        'add_staff' => '+ Add Staff',
        'staff_placeholder' => 'Staff Name',
        'company_name' => 'Company Name',
        'company_email' => 'Company Email',
        'company_address' => 'Company Address',
        'billing_address' => 'Billing Address',
        'notes' => 'Notes',
        'notes_helper' => 'Remaining characters: :count',
        'notes_warning' => 'Notes must not exceed 500 visible characters.',
        'company_name_helper' => 'Defaults to the name of the person in charge, Changeable.',
        'status' => 'Visibility Status',
        'status_active' => 'Active',
        'status_draft' => 'Draft',
        'status_helper' => 'Active clients are visible to all users. Draft clients are only visible to you.',
        'status_helper_readonly' => 'Only the user who created this resource can change the visibility status. Please contact',
        'extra_information' => 'Extra Information',
        'extra_title' => 'Title',
        'extra_value' => 'Value',
        'add_extra_info' => '+ Add Extra Information',
        'title_placeholder_short' => 'Title goes here',
    ],

    'table' => [
        'id' => 'ID',
        'pic_name' => 'Client Name (Company)',
        'country' => 'Country',
        'pic_contact_number' => 'Contact Number',
        'project_count' => 'Projects',
        'status' => 'Visibility',
        'status_active' => 'Active',
        'status_draft' => 'Draft',
        'important_url_count' => 'Important URLs',
        'created_at_by' => 'Created At (By)',
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
        'country_code' => 'Country Code',
    ],
];
