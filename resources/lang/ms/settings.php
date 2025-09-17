<?php

return [
    // Page navigation and titles
    'page' => [
        'navigation_label' => 'Tetapan',
        'title' => 'Tetapan',
    ],

    // Actions
    'actions' => [
        'save' => 'Simpan',
    ],

    // Section headers and descriptions
    'sections' => [
        'api' => 'Akses API',
        'api_description' => 'Urus kunci API anda untuk akses luaran ke data anda.',
        'location' => 'Lokasi',
        'location_description' => 'Tetapkan lokasi anda untuk data cuaca dan perkhidmatan geolokasi.',
        'timezone' => 'Zon Masa',
        'timezone_description' => 'Tetapkan zon masa anda untuk aplikasi.',
        'chatbot_history' => 'Sejarah Chatbot',
        'chatbot_history_description' => 'Urus sandaran perbualan chatbot anda dan pulihkan perbualan sebelumnya.',
    ],

    // API-related translations
    'api' => [
        'current_key' => 'Kunci API Anda',
        'no_key' => 'Tiada kunci API dijana',
        'helper' => 'Kunci API anda digunakan untuk mengesahkan permintaan ke titik akhir API.',
        'copy' => 'Salin Kunci API',
        'generate' => 'Jana Kunci API',
        'regenerate' => 'Jana Semula Kunci API',
        'delete' => 'Padam Kunci API',
        'documentation' => 'Dokumentasi API',
        'documentation_description' => 'Lihat titik akhir API, kaedah pengesahan, dan contoh penggunaan.',
        'confirm_regenerate' => 'Jana Semula Kunci API?',
        'confirm_regenerate_description' => 'Ini akan menjana kunci API baru dan menjadikan kunci semasa tidak sah. Sebarang aplikasi yang menggunakan kunci lama akan berhenti berfungsi.',
        'confirm_delete' => 'Padam Kunci API?',
        'confirm_delete_description' => 'Ini akan memadam kunci API anda secara kekal. Sebarang aplikasi yang menggunakan kunci ini akan berhenti berfungsi.',
        'regenerate_action' => 'Jana Semula',
        'delete_action' => 'Padam',
        'documentation_content' => [
            'base_url' => 'URL Dasar',
            'api_header' => 'Header API',
            'authentication' => 'Autentikasi',
            'example_request' => 'Contoh Request',
            'sample_screenshot' => 'Contoh Screenshot',
            'list_of_supported_api' => 'Senarai API yang Disokong',
        ],
    ],

    // Location-related translations
    'location' => [
        'settings' => 'Tetapan Lokasi',
        'city' => 'Bandar',
        'country' => 'Negara',
        'latitude' => 'Latitud',
        'longitude' => 'Longitud',
        'detect' => 'Set Lokasi',
        'clear' => 'Padam Lokasi',
    ],

    // Timezone-related translations
    'timezone' => [
        'settings' => 'Tetapan Zon Masa',
        'current_time_preview' => 'Pratonton Masa & Zon Masa',
        'select_to_preview' => 'Pilih zon masa untuk melihat pratonton masa semasa.',
        'preview_description' => 'Pratonton masa semasa dalam zon masa anda.',
        'current_time' => 'Masa Semasa',
        'information' => 'Maklumat Zon Masa (TZ)',
        'identifier_name' => 'Nama Pengenalan',
        'country_code' => 'Negara (Kod Negara)',
        'utc_offset' => 'Sisihan UTC (±hh:mm)',
        'abbreviation' => 'Singkatan',
        'sample_data_preview' => 'Pratonton Data Contoh',
        'id' => 'ID',
        'title' => 'Tajuk',
        'created_at' => 'Dicipta Pada',
        'updated_at' => 'Dikemaskini Pada',
        'by' => 'Oleh',
        'sample_project_alpha' => 'Projek Alpha',
        'sample_task_review' => 'Semakan Tugasan',
    ],

    // Weather-related translations
    'weather' => [
        'preview' => 'Pratonton Cuaca',
        'preview_description' => 'Pratonton cuaca semasa untuk lokasi yang dipilih.',
        'data_unavailable' => 'Data cuaca tidak tersedia',
        'error' => 'Tidak dapat mengambil data cuaca',
        'feels_like' => 'Terasa seperti',
        'no_location_data_available' => 'Tiada data lokasi tersedia',
    ],

    // Notification messages
    'notifications' => [
        'api_key_generated' => 'Kunci API Dijana',
        'api_key_generated_body' => 'Kunci API baru anda telah berjaya dijana.',
        'api_key_regenerated' => 'Kunci API Dijana Semula',
        'api_key_regenerated_body' => 'Kunci API anda telah dijana semula. Kunci lama tidak lagi sah.',
        'api_key_deleted' => 'Kunci API Dipadam',
        'api_key_deleted_body' => 'Kunci API anda telah berjaya dipadam.',
        'api_key_copied' => 'Kunci API Disalin',
        'api_key_copied_body' => 'Kunci API anda telah berjaya disalin ke papan klip.',
        'api_key_copying' => 'Menyalin Kunci API',
        'api_key_copying_body' => 'Kunci API sedang disalin ke dalam papan klip.',
        'api_key_copy_failed' => 'Gagal Menyalin Kunci API',
        'api_key_copy_failed_body' => 'Gagal menyalin kunci API ke dalam papan klip.',
        'location_detection_started' => 'Pengesanan Lokasi Dimulakan',
        'location_detection_started_body' => 'Sila benarkan akses lokasi dalam pelayar anda untuk mengesan lokasi semasa anda.',
        'location_detected' => 'Lokasi Dikesan',
        'location_detected_body' => 'Lokasi anda telah dikesan: :city, :country',
        'location_detection_failed' => 'Pengesanan Lokasi Gagal',
        'location_detection_failed_body' => 'Tidak dapat mengesan lokasi anda. Sila periksa kebenaran pelayar anda atau masukkan lokasi secara manual.',
        'location_cleared' => 'Lokasi Dihapus',
        'location_cleared_body' => 'Data lokasi anda telah dihapus.',
        'settings_saved' => 'Tetapan Disimpan',
        'settings_saved_body' => 'Tetapan anda telah berjaya disimpan.',
        'backup_created' => 'Sandaran Dibuat',
        'backup_created_body' => 'Sandaran ":name" telah berjaya dibuat.',
        'backup_failed' => 'Sandaran Gagal',
        'backups_refreshed' => 'Sandaran Dikemas Kini',
        'backups_refreshed_body' => 'Senarai sandaran telah dikemas kini.',
    ],

    // Chatbot-related translations
    'chatbot' => [
        'create_backup' => 'Buat Sandaran',
        'backup_id' => 'ID',
        'backup_name' => 'Tajuk Sandaran',
        'backup_type' => 'Jenis',
        'backup_messages' => 'Jumlah Mesej',
        'backup_date_range' => 'Julat Tarikh',
        'backup_backed_up' => 'Disandarkan Pada',
        'backup_size' => 'Saiz',
        'backup_actions' => 'Tindakan',
        'showing' => 'Menunjukkan :shown daripada :total sandaran',
        'load_more' => 'Muat :count sandaran lagi',
        'loading' => 'Memuatkan...',
        'no_backups' => 'Tiada sandaran lagi',
        'no_backups_description' => 'Buat sandaran pertama anda untuk menyimpan perbualan chatbot anda.',

        'filter' => [
            'reset' => 'Set Semula',
            'label' => 'Tapisan',
            'backup_type' => 'Jenis Sandaran',
            'all_types' => 'Semua jenis sandaran',
            'types' => [
                'weekly' => 'Sandaran mingguan',
                'manual' => 'Sandaran manual',
                'import' => 'Sandaran import',
            ],
        ],

        'search' => [
            'placeholder' => 'Cari',
            'clear' => 'Padam carian',
        ],

        'actions_menu' => [
            'title' => 'Tindakan',
            'download' => 'Muat Turun Sandaran',
            'restore' => 'Pulihkan Sandaran',
            'delete' => 'Padam Sandaran',
            'clear_filters' => 'Padam tapisan',
        ],

        'empty' => [
            'no_results_title' => 'Tiada sandaran ditemui',
            'no_results_both' => 'Tiada sandaran sepadan dengan carian ":search" dan jenis ":type"',
            'no_results_search' => 'Tiada sandaran sepadan dengan carian ":search"',
            'no_results_type' => 'Tiada sandaran ditemui untuk jenis ":type"',
        ],
    ],
];
