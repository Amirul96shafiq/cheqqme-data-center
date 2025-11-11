<?php

return [
    'user-menu' => [
        'tooltip' => [
            'greeting' => 'Menu Greeting',
        ],

        'profile-label' => 'Profil',
        'settings-label' => 'Tetapan',
        'whats-news-label' => 'Apa Yang Baru? (Changelog) 🡥',
        'calendar-label' => 'Kalendar Acara 🡥',
        'logout-label' => 'Log Keluar',
    ],

    'widgets' => [
        'welcome_back' => 'Selamat Kembali, :name',
        'dashboard_subtitle' => 'Tengoklah apa yang berlaku didalam CheQQme Data Center hari ini',
        'view_github' => 'Lihat di GitHub 🡥',
        'view_calendar' => 'Lihat Kalendar Acara 🡥',
        'view_changelog' => 'Lihat Apa Yang Baru? (Changelog) 🡥',
    ],

    'recent_documents' => [
        'title' => 'Dokumen Terkini',
        'id' => 'ID',
        'document_title' => 'Tajuk Dokumen',
        'file_type' => 'Jenis Fail',
        'project_title' => 'Tajuk Projek',
        'type' => 'Jenis',
        'internal' => 'Dalaman',
        'external' => 'Luaran',
        'created_at' => 'Dicipta Pada',
    ],

    'recent_projects' => [
        'title' => 'Projek Terkini',
        'id' => 'ID',
        'project_title' => 'Tajuk Projek',
        'issue_tracker' => 'Pelapor Isu',
        'tracking_tokens' => 'Token Penjejakan',
        'created_at' => 'Dicipta Pada',
    ],

    'total_clients' => [
        'title' => 'Jumlah Pelanggan',
    ],

    'total_projects' => [
        'title' => 'Jumlah Projek',
    ],

    'total_documents' => [
        'title' => 'Jumlah Dokumen',
    ],

    'total_tasks' => [
        'title' => 'Jumlah Tugasan',
    ],

    'your_tasks' => [
        'title' => 'Tugasan Anda',
        'description' => 'Lihat Semua :total Tugasan →',
    ],

    'your_issue_trackers' => [
        'title' => 'Penjejak Isu Anda',
        'description' => 'Lihat Semua :total Penjejak Isu →',
    ],

    'your_meeting_links' => [
        'title' => 'Pautan Mesyuarat Anda',
        'description' => 'Lihat Semua :total Pautan Mesyuarat →',
    ],

    'total_trello_boards' => [
        'title' => 'Jumlah Papan Trello',
    ],

    'total_important_urls' => [
        'title' => 'Jumlah URL Penting',
    ],

    'total_phone_numbers' => [
        'title' => 'Jumlah Nombor Telefon',
    ],

    'tabs' => [
        'overview' => 'Tinjauan',
        'analytics' => 'Analisis',
    ],

    'analytics' => [
        'task_status_distribution' => [
            'heading' => 'Pengagihan Status Tugasan',
            'description' => 'Pengagihan tugasan mengikut status semasa mereka',
            'other_issue_trackers' => 'Penjejak Isu Lain',
        ],
        'user_productivity' => [
            'heading' => 'Produktiviti Pengguna',
            'description' => 'Aktiviti setiap pengguna: tugasan, komen, sumber & mesyuarat',
            'series' => [
                'completed_tasks' => 'Tugasan Selesai & Dihapuskan',
                'comments_made' => 'Komen Dibuat',
                'resources_created' => 'Sumber Dicipta',
                'meetings_joined' => 'Mesyuarat Dihadiri',
            ],
            'tooltip' => [
                'completed_tasks' => 'tugasan ditugaskan selesai & dihapuskan',
                'comments_made' => 'komen dibuat',
                'resources_created' => 'sumber dicipta (hover bar individu untuk butiran)',
                'meetings_joined' => 'mesyuarat dihadiri',
            ],
        ],
        'chatbot_usage' => [
            'heading' => 'Penggunaan Pembantu AI',
            'description' => 'Perbualan chatbot dan penggunaan API dari masa ke semasa',
            'series' => [
                'conversations' => 'Perbualan',
                'api_calls' => 'Panggilan API',
            ],
            'filters' => [
                'quick_filter' => 'Penapis Cepat',
                'today' => 'Hari Ini',
                'yesterday' => 'Semalam',
                'this_week' => 'Minggu Ini',
                'this_month' => 'Bulan Ini',
                'date_start' => 'Tarikh Mula',
                'date_end' => 'Tarikh Akhir',
            ],
        ],
    ],

    'actions' => [
        'view_all' => 'Lihat Semua',
        'view' => 'Lihat',
        'edit' => 'Sunting',
        'toggle_view' => 'Tukar Paparan',
        'view_all_clients' => 'Lihat Semua Pelanggan →',
        'view_all_projects' => 'Lihat Semua Projek →',
        'view_all_documents' => 'Lihat Semua Dokumen →',
        'view_all_tasks' => 'Lihat Semua Tugasan →',
        'view_all_trello_boards' => 'Lihat Semua Papan Trello →',
        'view_all_important_urls' => 'Lihat Semua URL Penting →',
        'view_all_phone_numbers' => 'Lihat Semua Nombor Telefon →',
    ],
];
