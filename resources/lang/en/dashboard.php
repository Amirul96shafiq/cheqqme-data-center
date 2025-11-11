<?php

return [
    'user-menu' => [
        'tooltip' => [
            'greeting' => 'Greeting Menu',
        ],

        'profile-label' => 'Profile',
        'settings-label' => 'Settings',
        'whats-news-label' => "What's New? (Changelog) 🡥",
        'calendar-label' => 'Event Calendar 🡥',
        'logout-label' => 'Logout',
    ],

    'widgets' => [
        'welcome_back' => 'Welcome Back, :name',
        'dashboard_subtitle' => 'Here\'s what happening with CheQQme Data Center today',
        'view_github' => 'View on GitHub 🡥',
        'view_calendar' => 'View Event Calendar 🡥',
        'view_changelog' => 'View What\'s New? (Changelog) 🡥',
    ],

    'recent_documents' => [
        'title' => 'Recent Documents',
        'id' => 'ID',
        'document_title' => 'Document Title',
        'file_type' => 'File Type',
        'project_title' => 'Project Title',
        'type' => 'Type',
        'internal' => 'Internal',
        'external' => 'External',
        'created_at' => 'Created At',
    ],

    'recent_projects' => [
        'title' => 'Recent Projects',
        'id' => 'ID',
        'project_title' => 'Project Title',
        'issue_tracker' => 'Issue Tracker',
        'tracking_tokens' => 'Tracking Tokens',
        'created_at' => 'Created At',
    ],

    'total_clients' => [
        'title' => 'Total Clients',
    ],

    'total_projects' => [
        'title' => 'Total Projects',
    ],

    'total_documents' => [
        'title' => 'Total Documents',
    ],

    'total_tasks' => [
        'title' => 'Total Tasks',
    ],

    'your_tasks' => [
        'title' => 'Your Assigned Tasks',
        'description' => 'View all :total Tasks →',
    ],

    'your_issue_trackers' => [
        'title' => 'Your Assigned Issues',
        'description' => 'View all :total Issue Trackers →',
    ],

    'your_meeting_links' => [
        'title' => 'Your Meeting Links',
        'description' => 'View all :total Meeting Links →',
    ],

    'total_trello_boards' => [
        'title' => 'Total Trello Boards',
    ],

    'total_important_urls' => [
        'title' => 'Total Important URLs',
    ],

    'total_phone_numbers' => [
        'title' => 'Total Phone Numbers',
    ],

    'tabs' => [
        'overview' => 'Overview',
        'analytics' => 'Analytics',
    ],

    'analytics' => [
        'task_status_distribution' => [
            'heading' => 'Task Status Distribution',
            'description' => 'Distribution of tasks by their current status',
            'other_issue_trackers' => 'Other Issue Trackers',
            'filters' => [
                'users' => 'Users',
                'all_users' => 'All Users',
                'card_type' => 'Card Type',
                'all_cards' => 'All Cards',
                'tasks' => 'Tasks',
                'issue_trackers' => 'Issue Trackers',
            ],
        ],
        'user_productivity' => [
            'heading' => 'User Productivity',
            'description' => 'Each user\'s activities: tasks, comments, resources, etc.',
            'series' => [
                'completed_tasks' => 'Tasks Completed',
                'comments_made' => 'Comments Made',
                'resources_created' => 'Resources Created',
                'meetings_joined' => 'Meetings Joined',
            ],
            'tooltip' => [
                'completed_tasks' => 'assigned tasks completed & archived',
                'comments_made' => 'comments made',
                'resources_created' => 'resources created (hover individual bars for details)',
                'meetings_joined' => 'meetings joined',
            ],
            'filters' => [
                'quick_filter' => 'Quick Filter',
                'users' => 'Users',
                'all_users' => 'All Users',
                'today' => 'Today',
                'yesterday' => 'Yesterday',
                'this_week' => 'This Week',
                'this_month' => 'This Month',
                'this_year' => 'This Year',
                'overall' => 'Overall',
                'date_start' => 'Start Date',
                'date_end' => 'End Date',
            ],
        ],
        'chatbot_usage' => [
            'heading' => 'AI Assistant Usage',
            'description' => 'Chatbot conversations and API usage over time',
            'series' => [
                'conversations' => 'Conversations',
                'api_calls' => 'API Calls',
            ],
            'filters' => [
                'quick_filter' => 'Quick Filter',
                'users' => 'Users',
                'all_users' => 'All Users',
                'today' => 'Today',
                'yesterday' => 'Yesterday',
                'this_week' => 'This Week',
                'this_month' => 'This Month',
                'date_start' => 'Start Date',
                'date_end' => 'End Date',
            ],
        ],
    ],

    'actions' => [
        'view_all' => 'View All',
        'view' => 'View',
        'edit' => 'Edit',
        'toggle_view' => 'Toggle View',
        'view_all_clients' => 'View All Clients →',
        'view_all_projects' => 'View All Projects →',
        'view_all_documents' => 'View All Documents →',
        'view_all_tasks' => 'View All Tasks →',
        'view_all_trello_boards' => 'View All Trello Boards →',
        'view_all_important_urls' => 'View All Important URLs →',
        'view_all_phone_numbers' => 'View All Phone Numbers →',
    ],
];
