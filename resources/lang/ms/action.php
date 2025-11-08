<?php

return [
    'title' => 'Papan Tindakan',
    'navigation' => [
        'group' => 'Papan',
        'label' => 'Papan Tindakan',
    ],

    'card_label' => 'Tugasan Tindakan',
    'card_label_plural' => 'Tugasan Tindakan',

    'status' => [
        'issue_tracker' => 'Penjejak Isu',
        'todo' => 'Untuk Dilakukan',
        'in_progress' => 'Sedang Berjalan',
        'toreview' => 'Untuk Semakan',
        'completed' => 'Selesai',
        'archived' => 'Dihapus',
    ],

    'modal' => [
        'create_title' => 'Cipta Tugasan Tindakan',
        'edit_title' => 'Sunting Tugasan Tindakan',
    ],

    'notifications' => [
        'created' => 'Tugasan berjaya dicipta',
        'updated' => 'Tugasan berjaya dikemas kini',
    ],

    'form' => [
        'task_information' => 'Maklumat Tugasan',
        'additional_information' => 'Maklumat Tambahan Tugasan',
        'extra_information' => 'Maklumat Tambahan',
        'assign_to' => 'Ditugaskan Kepada',
        'due_date' => 'Tarikh Akhir',
        'status' => 'Status',
        'description' => 'Penerangan',
        'title' => 'Tajuk',
        'value' => 'Nilai',
        'comments' => 'Komen',
        'title_placeholder' => 'Masukkan tajuk tugasan',
        'title_placeholder_short' => 'Tajuk di sini',
        'user_with_id' => 'Pengguna #:id',
        'deleted_suffix' => ' (dipadam)',
    ],

    'create' => [
        'description_helper' => 'Baki aksara: :count',
        'description_warning' => 'Penerangan tidak boleh melebihi 500 aksara yang boleh dibaca.',
        'extra_information_helper' => 'Baki aksara: :count',
        'extra_information_warning' => 'Nilai tidak boleh melebihi 500 aksara yang boleh dibaca.',
    ],
    'edit' => [
        'description_helper' => 'Baki aksara: :count',
        'description_warning' => 'Penerangan tidak boleh melebihi 500 aksara yang boleh dibaca.',
        'extra_information_helper' => 'Baki aksara: :count',
        'extra_information_warning' => 'Nilai tidak boleh melebihi 500 aksara yang boleh dibaca.',
    ],

    'search_placeholder' => 'Cari tugasan',
    'clear_search' => 'Kosongkan carian',
    'currently_viewing' => 'Sedang melihat',
    'filter_tasks' => 'Tapis tugasan',
    'filters' => 'Tapisan',
    'reset' => 'Tetapkan Semula Tapisan',
    'show_all' => 'Papar Semua',
    'show_less' => 'Papar Sedikit',
    'more' => 'lagi',
    'toggle_featured_images' => 'Togol Imej Pilihan',
    'toggle_featured_images_tooltip' => 'Papar atau sembunyikan imej pilihan',
    'show_options' => 'Papar Pilihan',
    'hide_options' => 'Sembunyikan Pilihan',

    'filter' => [
        'assigned_to' => 'Ditugaskan Kepada',
        'all_users' => 'Semua Pengguna',
        'current_user' => 'Anda',
        'select_users' => 'Pilih pengguna untuk menapis',
        'selected_users' => 'Pengguna Terpilih',
        'users_selected' => 'pengguna dipilih',
        'card_type' => 'Jenis Kad',
        'select_card_type' => 'Pilih penapis jenis kad',
        'card_type_all' => 'Semua kad',
        'card_type_tasks' => 'Tugasan',
        'card_type_issue_trackers' => 'Penjejak Isu',
        'selected_card_type' => 'Jenis Kad Terpilih',
        'selected_label' => 'Terpilih',
        'due_date' => 'Tarikh Akhir',
        'due_today' => 'Tarikh akhir hari ini',
        'due_this_week' => 'Tarikh akhir minggu ini',
        'due_this_month' => 'Tarikh akhir bulan ini',
        'due_this_year' => 'Tarikh akhir tahun ini',
        'select_date_range' => 'Atau pilih julat tarikh tersuai',
        'select_due_date' => 'Pilih penapis tarikh akhir',
        'selected_due_date' => 'Tarikh Akhir Terpilih',
        'priority' => 'Keutamaan',
        'select_priority' => 'Pilih penapis keutamaan',
        'priorities_selected' => 'keutamaan dipilih',
        'selected_priority' => 'Keutamaan Terpilih',
        'priority_high' => 'Keutamaan Tinggi',
        'priority_medium' => 'Keutamaan Sederhana',
        'priority_low' => 'Keutamaan Rendah',
        'quick_filters' => 'Penapis Pantas',
        'custom_range' => 'Julat Tersuai',
        'from_date' => 'Dari Tarikh',
        'to_date' => 'Hingga Tarikh',
    ],

    'no_results' => [
        'title' => 'Tiada tugasan dijumpai',
        'description' => 'Cuba sesuaikan istilah carian anda atau kosongkan carian untuk melihat semua tugasan.',
        'clear_button' => 'Kosongkan carian',
        'search' => [
            'title' => 'Tiada tugasan dijumpai',
            'description' => 'Cuba sesuaikan istilah carian anda atau kosongkan carian untuk melihat semua tugasan.',
        ],
        'assigned_to' => [
            'title' => 'Tiada tugasan dijumpai untuk pengguna terpilih',
            'description' => 'Cuba sesuaikan penapis pengguna anda atau kosongkan penapis untuk melihat semua tugasan.',
        ],
        'due_date' => [
            'title' => 'Tiada tugasan dijumpai untuk julat tarikh terpilih',
            'description' => 'Cuba sesuaikan penapis tarikh anda atau kosongkan penapis untuk melihat semua tugasan.',
        ],
        'priority' => [
            'title' => 'Tiada tugasan dijumpai untuk keutamaan terpilih',
            'description' => 'Cuba sesuaikan penapis keutamaan anda atau kosongkan penapis untuk melihat semua tugasan.',
        ],
        'card_type' => [
            'title' => 'Tiada tugasan dijumpai untuk jenis kad terpilih',
            'description' => 'Cuba ubah penapis jenis kad anda atau kosongkan penapis untuk melihat semua tugasan.',
        ],
    ],
];
