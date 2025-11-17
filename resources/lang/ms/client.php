<?php

return [
    'navigation_group' => 'Sumber',
    'navigation_label' => 'Pelanggan',

    'labels' => [
        'singular' => 'Pelanggan',
        'plural' => 'Pelanggan',
        'edit-client' => 'Kemaskini Pelanggan',
    ],

    'navigation' => [
        'labels' => 'Pelanggan',
    ],

    'actions' => [
        'create' => 'Tambah Pelanggan',
        'make_draft' => 'Tukar ke Draf',
        'make_active' => 'Tukar ke Aktif',
        'make_draft_tooltip' => 'Tukar pelanggan ini ke status Draf (hanya anda boleh melihatnya)',
        'make_active_tooltip' => 'Tukar pelanggan ini ke status Aktif (semua pengguna boleh melihatnya)',
        'status_updated' => 'Status dikemaskini berjaya',
        'client_activated' => 'Pelanggan kini Aktif dan boleh dilihat oleh semua pengguna.',
        'client_made_draft' => 'Pelanggan kini dalam status Draf dan boleh dilihat oleh anda sahaja.',
    ],

    'section' => [
        'client_info' => 'Maklumat Pengurus',
        'staff_info' => 'Maklumat Kakitangan',
        'staff_info_description' => 'Tambah secara manual atau Tambah secara automatik dari Issue Tracker',
        'company_info' => 'Maklumat Syarikat Pelanggan',
        'company_projects' => 'Projek Syarikat Pelanggan',
        'extra_info' => 'Maklumat Tambahan Pelanggan',
        'status_info' => 'Maklumat Keterlihatan Sumber',
        'important_urls' => 'URL Penting Pelanggan',
        'activity_logs' => 'Log Aktiviti',
    ],

    'form' => [
        'pic_name' => 'Nama PIC',
        'pic_email' => 'Emel PIC',
        'pic_contact_number' => 'Nombor Telefon PIC',
        'staff_information' => 'Maklumat Kakitangan',
        'staff_name' => 'Nama Kakitangan',
        'staff_contact_number' => 'Nombor Telefon Kakitangan',
        'staff_email' => 'Emel Kakitangan',
        'add_staff' => '+ Tambah Kakitangan',
        'staff_placeholder' => 'Nama Kakitangan',
        'company_name' => 'Nama Syarikat',
        'company_email' => 'Emel Syarikat',
        'company_address' => 'Alamat Syarikat',
        'billing_address' => 'Alamat Invois',
        'notes' => 'Catatan',
        'notes_helper' => 'Baki aksara: :count',
        'notes_warning' => 'Catatan tidak boleh melebihi 500 aksara yang boleh dibaca.',
        'company_name_helper' => 'Akan guna nama PIC secara automatik, tapi boleh diubah.',
        'status' => 'Status Keterlihatan',
        'status_active' => 'Aktif',
        'status_draft' => 'Draf',
        'status_helper' => 'Pelanggan Aktif boleh dilihat oleh semua pengguna. Pelanggan Draf hanya boleh dilihat oleh anda.',
        'extra_information' => 'Maklumat Tambahan',
        'extra_title' => 'Tajuk',
        'extra_value' => 'Nilai',
        'add_extra_info' => '+ Tambah Maklumat Tambahan',
        'title_placeholder_short' => 'Tajuk di sini',
    ],

    'table' => [
        'id' => 'ID',
        'pic_name' => 'Nama Pelanggan (Syarikat)',
        'country' => 'Negara',
        'pic_contact_number' => 'Nombor Telefon PIC',
        'project_count' => 'Projek',
        'status' => 'Keterlihatan',
        'status_active' => 'Aktif',
        'status_draft' => 'Draf',
        'important_url_count' => 'URL Penting',
        'created_at_by' => 'Dicipta Pada (Oleh)',
        'updated_at_by' => 'Dikemas Kini pada (oleh)',
        'tooltip' => [
            'full_name' => 'Nama Penuh',
            'company' => 'Syarikat',
        ],
    ],

    'search' => [
        'pic_email' => 'Emel PIC',
        'pic_contact_number' => 'Nombor Telefon PIC',
        'company_name' => 'Nama Syarikat',
    ],

    'filter' => [
        'trashed' => 'Dihapus',
        'country_code' => 'Kod Negara',
    ],
];
