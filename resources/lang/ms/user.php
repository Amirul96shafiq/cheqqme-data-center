<?php

return [
    // Navigation
    'navigation_group' => 'Pengguna',
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
        'connection_settings' => 'Pengaturan Sambungan',
        'connection_settings_description' => 'Urus sambungan akaun dan integrasi anda',
        'password_settings' => 'Pengaturan Kata Laluan',
        'user_info' => 'Maklumat Pengguna',
        'password_info' => 'Maklumat Kata Laluan',
        'password_info_description' => 'Aktifkan toggle Tukar Kata Laluan? untuk melihat bahagian ini',
        'password_info_description_profile' => 'Tukar dan kemaskini kata laluan akaun anda di sini',
        'danger_zone' => 'Zon Bahaya',
        'danger_zone_description' => 'Aktifkan toggle Padam Pengguna? untuk melihat bahagian ini',
        'activity_logs' => 'Log Aktiviti',
    ],

    // Form Fields
    'form' => [
        'avatar' => 'Avatar',
        'cover_image' => 'Gambar Cover',
        'cover_image_helper' => 'Muat naik gambar cover (disyorkan: maksimum 20MB)',
        'web_app_background' => 'Latar Belakang Bergambar',
        'web_app_background_helper' => 'Aktifkan gambar latar belakang tersuai untuk CheQQme Data Center',
        'background_preview' => 'Pratonton Latar Belakang',
        'enabled' => 'Diaktifkan: Mod Bergambar',
        'disabled' => 'Dinyahaktifkan: Mod Fokus',
        'username' => 'Gelaran Pengguna',
        'name' => 'Nama',
        'email' => 'Emel',
        'online_status' => 'Status Dalam Talian',
        'online_status_helper' => 'Pilih status dalam talian anda yang akan kelihatan kepada pengguna lain',
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
        'connected' => 'Disambungkan',
        'not_connected' => 'Tidak Disambungkan',

        // Microsoft Connection
        'connect_microsoft' => 'Akan Datang',
        'microsoft_coming_soon' => 'Akan Datang',
    ],

    // Online Status Indicator
    'indicator' => [
        // Online Status Options
        'online_status_online' => 'Dalam Talian',
        'online_status_away' => 'Jauh',
        'online_status_dnd' => 'Jangan Ganggu',
        'online_status_invisible' => 'Tidak Kelihatan',
        
        // Online Status Messages
        'online_status_updated' => 'Status berjaya dikemaskini.',
        'online_status_update_failed' => 'Gagal mengemaskini status',
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
        'chatbot-history' => 'Sejarah Chatbot',
    ],

    // Filters
    'filter' => [
        'has_cover_image' => 'Ada Gambar Cover',
        'timezone' => 'Zon Waktu',
        'trashed' => 'Dihapus',
    ],

    // Notifications
    'notifications' => [
        'saved' => 'Disimpan',
        'saved_body' => 'Muat semula halaman untuk melihat perubahan.',
        'saved_password' => 'Disimpan',
        'saved_password_body' => 'Muat semula halaman untuk melihat perubahan.',
        'validation_error' => 'Kesilapan Pengesahan',
        'old_password_incorrect' => 'Kata laluan lama tidak betul.',
    ],
];
