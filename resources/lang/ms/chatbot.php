<?php

return [
    // Basic chatbot messages
    'welcome_message' => 'Hai! Saya :ai_name. Asisten AI terpandai & terbijaksana. Bagaimana saya boleh membantu Anda hari ini?',
    'ai_name' => 'Arem AI',
    'ready_message' => 'OTW Bosskur! Apa yang ingin Anda kerjakan hari ini?',
    'help_message' => 'Gunakan :help_command untuk memanggil fungsi yang sedia ada!',
    'help_command' => '/help',
    'thinking_message' => 'Arem sedang berfikir...',
    'error_message' => 'Maaf, saya menghadapi ralat. Sila cuba lagi.',
    'clearing_message' => 'Menghapus perbualan...',
    'clear_confirmation_message' => 'Adakah anda pasti ingin menghapus perbualan? Semua perbualan akan diset semula. (Anda masih boleh pulihkan melalui backup dari Sejarah Chatbot)',
    'clear_success_message' => 'Perbualan berjaya dihapus!',

    // Actions
    'action' => [
        'open_chat' => 'Sembang dengan Arem AI',
        'close_chat' => 'Tutup Chat',
        'clear_conversation' => 'Kosongkan Perbualan',
    ],

    // Header
    'header' => [
        'subheading01' => 'Asisten AI terpandai & terbijaksana~~',
        'subheading02' => 'Sorang je ke tu? Meh I teman.',
        'subheading3' => 'Tak jumpa-jumpa ke? Biar saya carikan!',
        'subheading04' => ' Saya suka awak. Awak suka saya. Kami adalah keluarga yang bahagia.',
        'subheading05' => 'Kita jaga kita.',
        'subheading06' => 'Pisang goreng ke goreng pisang? Hmm...',
    ],

    // History page
    'history' => [
        'navigation_label' => 'Sejarah Chatbot',
        'title' => 'Sejarah Chatbot',
        'create_backup' => 'Tambah Backup',
        'upload_backup' => 'Muat Naik Backup',
        'backups_title' => 'Backup Perbualan',
    ],

    // Table structure
    'table' => [
        'backup_id' => 'ID',
        'backup_name' => 'Tajuk Backup',
        'backup_type' => 'Jenis',
        'backup_messages' => 'Jumlah Mesej',
        'backup_date_range' => 'Julat Tarikh',
        'backup_backed_up' => 'Tarikh dan Masa Backup',
        'backup_size' => 'Saiz',
        'backup_actions' => 'Tindakan',
    ],

    // Actions
    'actions' => [
        'download' => 'Muat Turun',
        'restore' => 'Pulihkan',
        'delete' => 'Padam',
        'create_backup' => 'Tambah Backup',
        'creating_backup' => 'Membuat...',
    ],

    // Filters
    'filter' => [
        'reset' => 'Set Semula',
        'label' => 'Penapis',
        'backup_type' => 'Jenis Backup',
        'all_types' => 'Semua jenis backup',
        'types' => [
            'weekly' => 'Backup mingguan',
            'manual' => 'Backup manual',
            'import' => 'Backup import',
        ],
    ],

    // Search
    'search' => [
        'placeholder' => 'Cari',
        'clear' => 'Kosongkan carian',
    ],

    // Chat input
    'input' => [
        'placeholder' => 'Apa yang ada di dalam hati anda?',
    ],

    // Actions menu
    'actions_menu' => [
        'title' => 'Tindakan',
        'download' => 'Muat Turun Backup',
        'restore' => 'Pulihkan Backup',
        'delete' => 'Padam Backup',
        'clear_filters' => 'Kosongkan penapis',
    ],

    // Empty states
    'empty' => [
        'no_backups' => 'Tiada backup lagi',
        'no_backups_description' => 'Buat backup pertama untuk menyimpan perbualan chatbot anda.',
        'no_results_title' => 'Tiada backup dijumpai',
        'no_results_both' => 'Tiada backup yang sepadan dengan carian ":search" dan jenis backup ":type"',
        'no_results_search' => 'Tiada backup yang sepadan dengan carian ":search"',
        'no_results_type' => 'Tiada backup dijumpai untuk jenis backup ":type"',
    ],

    // Confirmation modals
    'confirm' => [
        'backup_creation' => 'Tambah Backup?',
        'backup_description' => 'Adakah anda pasti ingin membuat backup? Ini akan menyimpan semua perbualan chatbot semasa anda.',
        'backup_restore' => 'Pulihkan Backup?',
        'backup_restore_description' => 'Adakah anda pasti ingin memulihkan backup ini? Ini akan menambah perbualan dari backup ke chatbot semasa anda.',
        'backup_delete' => 'Padam Backup?',
        'backup_delete_description' => 'Adakah anda pasti ingin memadam backup ini? Tindakan ini tidak boleh dibatalkan.',
        'backup_download' => 'Muat Turun Backup?',
        'backup_download_description' => 'Adakah anda pasti ingin memuat turun fail backup ini?',
    ],

    // Tabs
    'tabs' => [
        'all' => 'Semua',
        'today' => 'Hari Ini',
        'this_week' => 'Minggu Ini',
        'this_month' => 'Bulan Ini',
        'this_year' => 'Tahun Ini',
    ],

    // Upload
    'upload' => [
        'modal_heading' => 'Muat Naik Backup',
        'modal_description' => 'Muat naik fail backup chatbot yang telah dimuat turun untuk diimport ke dalam sejarah anda.',
        'file_label' => 'Fail Backup',
        'file_helper' => 'Pilih fail backup JSON (maksimum 10MB)',
        'submit' => 'Muat Naik',
        'success' => 'Backup Berjaya Dimuat Naik',
        'success_body' => 'Backup ":name" telah diimport dengan :count mesej.',
        'failed' => 'Muat Naik Gagal',
        'no_file' => 'Tiada fail dimuat naik.',
        'read_failed' => 'Gagal membaca fail yang dimuat naik.',
        'invalid_json' => 'Fail yang dimuat naik bukan fail JSON yang sah.',
        'invalid_structure' => 'Fail backup mempunyai struktur yang tidak sah. Sila pastikan anda memuat naik fail backup chatbot yang sah.',
        'no_conversations' => 'Fail backup tidak mengandungi sebarang perbualan.',
    ],
];
