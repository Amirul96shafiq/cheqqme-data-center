<?php

return [
    // Basic chatbot messages
    'welcome_message' => 'Hello! I\'m :ai_name. The most genius AI assistant in the world. How can I assist you today?',
    'ai_name' => 'Arem AI',
    'ready_message' => 'Ready for a fresh start! What would you like to know or work on?',
    'help_message' => 'Use :help_command to call my available functions!',
    'help_command' => '/help',
    'thinking_message' => 'Arem is thinking...',
    'error_message' => 'Sorry, I encountered an error. Please try again.',
    'clearing_message' => 'Clearing conversation...',
    'clear_confirmation_message' => 'Are you sure you want to clear the conversation? All conversation will be resetted. (You can still restore it via backup from Chatbot History)',

    // Header
    'header' => [
        'subheading01' => 'Your brilliant assistant~~',
        'subheading02' => 'You look lonely, I can fix that.',
        'subheading03' => 'Boy have you lost your mind, cause I\'ll help you find it!',
        'subheading04' => ' I love you. You love me. We\'re a happy family.',
        'subheading05' => 'Monkey together strong.',
    ],

    // History page
    'history' => [
        'navigation_label' => 'Chatbot History',
        'title' => 'Chatbot History',
        'create_backup' => 'Create Backup',
        'backups_title' => 'Chatbot Backups',
    ],

    // Table structure
    'table' => [
        'backup_id' => 'ID',
        'backup_name' => 'Backup Title',
        'backup_type' => 'Type',
        'backup_messages' => 'Total Messages',
        'backup_date_range' => 'Date Range',
        'backup_backed_up' => 'Backed Up At',
        'backup_size' => 'Size',
        'backup_actions' => 'Actions',
    ],

    // Actions
    'actions' => [
        'download' => 'Download',
        'restore' => 'Restore',
        'delete' => 'Delete',
        'create_backup' => 'Create Backup',
        'creating_backup' => 'Creating...',
    ],

    // Filters
    'filter' => [
        'reset' => 'Reset',
        'label' => 'Filters',
        'backup_type' => 'Backup Type',
        'time_period' => 'Time Period',
        'all_types' => 'All backup types',
        'types' => [
            'weekly' => 'Weekly backups',
            'manual' => 'Manual backups',
            'import' => 'Import backups',
        ],
    ],

    // Search
    'search' => [
        'placeholder' => 'Search',
        'clear' => 'Clear search',
    ],

    // Actions menu
    'actions_menu' => [
        'title' => 'Actions',
        'download' => 'Download Backup',
        'restore' => 'Restore Backup',
        'delete' => 'Delete Backup',
        'clear_filters' => 'Clear filters',
    ],

    // Empty states
    'empty' => [
        'no_backups' => 'No backups yet',
        'no_backups_description' => 'Create your first backup to save your chatbot conversations.',
        'no_results_title' => 'No backups found',
        'no_results_both' => 'No backups match your search for ":search" and backup type ":type"',
        'no_results_search' => 'No backups match your search for ":search"',
        'no_results_type' => 'No backups found for backup type ":type"',
    ],

    // Confirmation modals
    'confirm' => [
        'backup_creation' => 'Create Backup?',
        'backup_description' => 'Are you sure you want to create a backup? This will save all your current chatbot conversations.',
        'backup_restore' => 'Restore Backup?',
        'backup_restore_description' => 'Are you sure you want to restore this backup? This will add the conversations from the backup to your current chatbot.',
        'backup_delete' => 'Delete Backup?',
        'backup_delete_description' => 'Are you sure you want to delete this backup? This action cannot be undone.',
        'backup_download' => 'Download Backup?',
        'backup_download_description' => 'Are you sure you want to download this backup file?',
    ],

    // Tabs
    'tabs' => [
        'all' => 'All',
        'today' => 'Today',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'this_year' => 'This Year',
    ],
];
