<?php

return [
    'navigation_group' => 'Sumber',
    'navigation_label' => 'Nombor Telefon',

    'labels' => [
        'singular' => 'Nombor Telefon',
        'plural' => 'Nombor Telefon',
        'edit-phone-number' => 'Kemaskini Nombor Telefon',
    ],

    'navigation' => [
        'labels' => 'Nombor Telefon',
    ],

    'actions' => [
        'create' => 'Tambah Nombor Telefon',
        'make_draft' => 'Tukar ke Draf',
        'make_active' => 'Tukar ke Aktif',
        'make_draft_tooltip' => 'Tukar nombor telefon ini ke Draf (Hanya anda boleh melihatnya)',
        'make_active_tooltip' => 'Tukar nombor telefon ini ke Aktif (semua pengguna boleh melihatnya)',
        'visibility_status_updated' => 'Status keterlihatan dikemaskini berjaya',
        'phone_number_activated' => 'Nombor telefon kini Aktif dan boleh dilihat oleh semua pengguna.',
        'phone_number_made_draft' => 'Nombor telefon kini dalam status Draf dan boleh dilihat oleh anda sahaja.',
    ],

    'section' => [
        'phone_number_info' => 'Maklumat Nombor Telefon',
        'extra_info' => 'Maklumat Tambahan Nombor Telefon',
        'activity_logs' => 'Log Aktiviti',
        'visibility_status' => 'Maklumat Keterlihatan Sumber',
    ],

    'form' => [
        'phone_number_title' => 'Tajuk Nombor Telefon',
        'phone_number' => 'Nombor Telefon',
        'notes' => 'Nota',
        'notes_helper' => 'Baki aksara: :count',
        'notes_warning' => 'Catatan tidak boleh melebihi 500 aksara yang boleh dilihat.',
        'extra_information' => 'Maklumat Tambahan',
        'extra_title' => 'Tajuk',
        'extra_value' => 'Nilai',
        'add_extra_info' => '+ Tambah Maklumat Tambahan',
        'title_placeholder_short' => 'Tajuk di sini',
        'visibility_status' => 'Status Keterlihatan',
        'visibility_status_active' => 'Aktif',
        'visibility_status_draft' => 'Draf',
        'visibility_status_helper' => 'Nombor Telefon Aktif boleh dilihat semua pengguna. Nombor Telefon Draf hanya boleh dilihat oleh pencipta.',
        'visibility_status_helper_readonly' => 'Hanya pengguna yang mencipta sumber ini boleh menukar status keterlihatan. Sila hubungi',
    ],

    'table' => [
        'id' => 'ID',
        'title' => 'Tajuk',
        'country' => 'Negara',
        'phone_number' => 'Nombor Telefon',
        'visibility_status' => 'Keterlihatan',
        'visibility_status_active' => 'Aktif',
        'visibility_status_draft' => 'Draf',
        'created_at_by' => 'Dicipta Pada (Oleh)',
        'updated_at_by' => 'Dikemaskini Pada (oleh)',
    ],

    'search' => [
        'phone' => 'Nombor Telefon',
    ],

    'filter' => [
        'trashed' => 'Dihapus',
        'country_code' => 'Kod Negara',
    ],
];
