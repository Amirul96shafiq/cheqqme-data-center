<?php

return [
    'navigation_group' => 'Pengurusan Data',
    'navigation_label' => 'Dokumen',

    'labels' => [
        'singular' => 'Dokumen',
        'plural' => 'Dokumen',
        'edit-document' => 'Kemaskini Dokumen',
    ],

    'navigation' => [
        'labels' => 'Dokumen',
    ],

    'actions' => [
        'create' => 'Tambah Dokumen',
    ],

    'section' => [
        'document_info' => 'Maklumat Dokumen',
        'extra_info' => 'Maklumat Tambahan Dokumen',
        'activity_logs' => 'Log Aktiviti',
    ],

    'form' => [
        'document_title' => 'Tajuk Dokumen',
        'project' => 'Projek',
        'client' => 'Pelanggan',
        'document_type' => 'Jenis Dokumen',
        'external' => 'Luar',
        'internal' => 'Dalaman',
        'document_url' => 'URL Dokumen',
        'document_url_note' => 'URL untuk dokumen luar',
        'open_url' => 'Buka URL',
        'document_url_helper' => 'Buka URL dalam tab baru',
        'document_upload' => 'Muat Naik Dokumen',
        'document_upload_helper' => 'Muat naik dokumen dalaman di sini (PDF, JPEG, PNG, DOC, DOCX, XLS, XLSX, CSV, PPT, PPTX) - maksimum 20MB',
        'notes' => 'Catatan',
        'notes_helper' => 'Baki aksara: :count',
        'notes_warning' => 'Catatan tidak boleh melebihi 500 aksara yang boleh dilihat.',
        'extra_information' => 'Maklumat Tambahan',
        'extra_title' => 'Tajuk',
        'extra_value' => 'Nilai',
        'add_extra_info' => '+ Tambah Maklumat Tambahan',
        'title_placeholder_short' => 'Tajuk di sini',
    ],

    'table' => [
        'id' => 'ID',
        'title' => 'Tajuk',
        'type' => 'Jenis',
        'project' => 'Projek',
        'created_at' => 'Dicipta Pada',
        'updated_at_by' => 'Dikemaskini Pada (oleh)',
        'internal' => 'Dalaman',
        'external' => 'Luar',
        'client' => 'Pelanggan',
        'document_url' => 'URL Dokumen',
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
        'type' => 'Jenis',
        'url' => 'URL',
        'file_path' => 'Laluan Fail',
    ],

    'filter' => [
        'trashed' => 'Dihapus',
    ],

    'drag_drop' => [
        'large_file_title' => 'Lebih daripada 5MB dikesan, sila muat naik secara manual',
        'large_file_message' => 'Fail besar dikesan (:sizeMB). Sila gunakan petak muat naik fail di bawah untuk memuat naik ":filename".',
        'file_too_large' => 'Saiz fail melebihi had 20MB. Fail anda ialah :sizeMBMB.',
        'unsupported_file_type' => 'Jenis fail tidak disokong. Sila muat naik fail PDF, Word, Excel, PowerPoint, imej, video, atau CSV.',
        'drop_file_to_upload_document' => 'Letakkan fail untuk muat naik dokumen',
        'drop_file_to_upload_document_helper' => 'Muat naik secara automatik untuk fail di bawah 5MB. Jika lebih besar daripada 5MB, anda boleh muat naik fail secara manual selepas ke halaman Dokumen.',
        'filament_upload_detected' => 'Petak muat naik Filament dikesan, gunakan drag-drop seperti biasa',
    ],
];
