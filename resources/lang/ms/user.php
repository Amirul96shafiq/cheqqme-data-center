<?php

return [
    // Navigation
    'navigation_group' => 'Pengurusan Pengguna',
    'navigation_label' => 'Pengguna',

    // Labels
    'labels' => [
        'singular' => 'Pengguna',
        'plural' => 'Pengguna',
        'edit-user' => 'Kemaskini Pengguna',
    ],

    // Actions
    'actions' => [
        'create' => 'Tambah Pengguna',
        'delete' => 'Padam Pengguna',
    ],

    // Sections
    'section' => [
        'profile_settings' => 'Pengaturan Profil',
        'profile_settings_description' => 'Konfigurasikan dan personalisasi pengaturan profil anda di sini',
        'google_connection_settings' => 'Pengaturan Sambungan',
        'google_connection_settings_description' => 'Urus sambungan akaun dan integrasi anda',
        'password_settings' => 'Pengaturan Kata Laluan',
        'user_info' => 'Maklumat Pengguna',
        'password_info' => 'Maklumat Kata Laluan',
        'password_info_description' => 'Aktifkan toggle Tukar Kata Laluan? untuk melihat bahagian ini',
        'password_info_description_profile' => '',
        'danger_zone' => 'Zon Bahaya',
        'danger_zone_description' => 'Aktifkan toggle Padam Pengguna? untuk melihat bahagian ini',
        'activity_logs' => 'Log Aktiviti',
    ],

    // Form Fields
    'form' => [
        'saved' => 'Disimpan.',
        'saved_body' => 'Sila log masuk semula atau muat semula halaman.',
        'saved_password' => 'Disimpan.',
        'saved_password_body' => 'Sila log masuk semula atau muat semula halaman.',
        'avatar' => 'Avatar',
        'cover_image' => 'Gambar Cover',
        'cover_image_helper' => 'Muat naik gambar cover (disyorkan: maksimum 20MB)',
        'username' => 'Gelaran Pengguna',
        'name' => 'Nama',
        'email' => 'Emel',
        'change_password' => 'Tukar Kata Laluan',
        'generate_password' => 'Hasilkan Kata Laluan Kuat',
        'old_password' => 'Kata Laluan Lama',
        'new_password' => 'Kata Laluan Baru',
        'password' => 'Kata Laluan',
        'confirm_password' => 'Sahkan Kata Laluan',
        'password_helper' => 'Sekurang-kurangnya 5 aksara',
        'confirm_new_password' => 'Sahkan Kata Laluan Baru',
        'user_deletion' => 'Penghapusan Pengguna',
        'user_confirm_title' => 'Pengesahan Penghapusan Pengguna',
        'user_confirm_placeholder' => 'Taipkan tepat CONFIRMED DELETE ACCOUNT (sensitif kepada huruf besar) untuk mengaktifkan butang padam di bawah',
        'user_confirm_helpertext' => 'CONFIRMED DELETE ACCOUNT',
        'name_helper' => 'Dilengkapi dengan gelaran pengguna jika kosong, boleh diubah',
        'personalize' => 'Personalisasi',
        'uploading' => 'Sedang memuat naik...',

        // Google Connection
        'disconnect_google' => 'Putuskan Sambungan',
        'disconnect_google_confirm' => 'Putuskan Sambungan Akaun Google',
        'disconnect_google_description' => 'Adakah anda pasti ingin memutuskan sambungan akaun Google anda? Anda tidak lagi boleh log masuk menggunakan Google, tetapi anda masih boleh menggunakan nama pengguna dan kata laluan.',
        'disconnect' => 'Putuskan Sambungan',
        'cancel' => 'Batal',
        'google_disconnected' => 'Akaun Google Diputuskan',
        'google_disconnected_body' => 'Akaun Google anda telah berjaya diputuskan. Anda masih boleh log masuk menggunakan nama pengguna dan kata laluan.',
        'google_connection' => 'Google oAuth',
        'google_description' => 'Sambungkan akaun Google anda untuk log masuk yang lebih cepat & menyesuaikan avatar Google dengan profil anda.',
        'connect_google' => 'Sambung Sekarang',
        'google_connected' => 'Akaun Google Disambungkan',
        'google_connected_body' => 'Akaun Google anda telah berjaya disambungkan. Anda kini boleh log masuk menggunakan Google.',
        'google_connection_failed' => 'Sambungan Google Gagal',
        'connection_status' => 'Status Sambungan',

        // Microsoft Connection
        'microsoft_connection' => 'Microsoft oAuth',
        'microsoft_description' => 'Sambungkan akaun Microsoft anda untuk log masuk yang lebih cepat & menyesuaikan avatar Microsoft dengan profil anda.',
        'connect_microsoft' => 'Sambung Sekarang',
        'disconnect_microsoft' => 'Putuskan Sambungan',
        'disconnect_microsoft_confirm' => 'Putuskan Sambungan Akaun Microsoft',
        'disconnect_microsoft_description' => 'Adakah anda pasti ingin memutuskan sambungan akaun Microsoft anda? Anda tidak lagi boleh log masuk menggunakan Microsoft, tetapi anda masih boleh menggunakan nama pengguna dan kata laluan.',
    ],

    // Table Columns
    'table' => [
        'id' => 'ID',
        'avatar' => 'Avatar',
        'username' => 'Username',
        'email' => 'Email',
        'created_at' => 'Created At',
        'updated_at_by' => 'Updated At (by)',
        'personalize' => 'Personalisasi',
        'settings' => 'Tetapan',
    ],

    // Filters
    'filter' => [
        'has_cover_image' => 'Ada Gambar Cover',
        'timezone' => 'Zon Waktu',
        'trashed' => 'Dihapus',
    ],
];
