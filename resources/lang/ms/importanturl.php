<?php

return [
    'navigation_group' => 'Sumber',
    'navigation_label' => 'URL Penting',

    'labels' => [
        'singular' => 'URL Penting',
        'plural' => 'URL Penting',
        'edit-important-url' => 'Kemaskini URL Penting',
    ],

    'navigation' => [
        'labels' => 'URL Penting',
    ],

    'actions' => [
        'create' => 'Tambah URL Penting',
        'make_draft' => 'Tukar ke Draf',
        'make_active' => 'Tukar ke Aktif',
        'make_draft_tooltip' => 'Tukar URL Penting ini ke status Draf (hanya anda boleh melihatnya)',
        'make_active_tooltip' => 'Tukar URL Penting ini ke status Aktif (semua pengguna boleh melihatnya)',
        'visibility_status_updated' => 'Status keterlihatan dikemaskini berjaya',
        'important_url_activated' => 'URL Penting kini Aktif dan boleh dilihat oleh semua pengguna.',
        'important_url_made_draft' => 'URL Penting kini dalam status Draf dan boleh dilihat oleh anda sahaja.',
    ],

    'section' => [
        'important_url_info' => 'Maklumat URL Penting',
        'extra_info' => 'Maklumat Tambahan URL Penting',
        'activity_logs' => 'Log Aktiviti',
        'visibility_status' => 'Maklumat Keterlihatan Sumber',
    ],

    'form' => [
        'important_url_title' => 'Tajuk URL Penting',
        'project' => 'Projek',
        'client' => 'Pelanggan',
        'important_url' => 'URL Penting',
        'important_url_note' => 'URL untuk pautan penting',
        'open_url' => 'Buka URL',
        'important_url_helper' => 'Buka URL dalam tab baru',
        'notes' => 'Nota',
        'notes_helper' => 'Baki aksara: :count',
        'notes_warning' => 'Catatan tidak boleh melebihi 500 aksara yang boleh dilihat.',
        'extra_information' => 'Maklumat Tambahan',
        'extra_title' => 'Tajuk',
        'extra_value' => 'Nilai',
        'add_extra_info' => '+ Tambah Maklumat Tambahan',
        'title_placeholder_short' => 'Tajuk di sini',
        'create_project' => 'Tambah Projek',
        'create_client' => 'Tambah Pelanggan',
        'visibility_status' => 'Status Keterlihatan',
        'visibility_status_active' => 'Aktif',
        'visibility_status_draft' => 'Draf',
        'visibility_status_helper' => 'URL Penting Aktif boleh dilihat oleh semua pengguna. URL Penting Draf hanya boleh dilihat oleh pencipta.',
        'visibility_status_helper_readonly' => 'Hanya pengguna yang mencipta sumber ini boleh menukar status keterlihatan. Sila hubungi',
    ],

    'table' => [
        'id' => 'ID',
        'title' => 'Tajuk',
        'link' => 'Link',
        'project' => 'Projek',
        'client' => 'Pelanggan',
        'important_url' => 'Link URL',
        'visibility_status' => 'Keterlihatan',
        'visibility_status_active' => 'Aktif',
        'visibility_status_draft' => 'Draf',
        'created_at_by' => 'Dicipta Pada (Oleh)',
        'updated_at_by' => 'Dikemas Kini Pada (oleh)',
        'tooltip' => [
            'full_name' => 'Nama Penuh',
            'company' => 'Syarikat',
        ],
    ],

    // Filters
    'filters' => [
        'client_id' => 'Pelanggan',
        'project_id' => 'Projek',
    ],

    'tabs' => [
        'all' => 'Semua',
        'today' => 'Hari Ini',
        'this_week' => 'Minggu Ini',
        'this_month' => 'Bulan Ini',
        'this_year' => 'Tahun Ini',
    ],

    'search' => [
        'project' => 'Projek',
        'client' => 'Pelanggan',
        'url' => 'URL',
    ],

    'filter' => [
        'trashed' => 'Dihapus',
    ],
];
