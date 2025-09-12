<?php

return [
  /*
  |--------------------------------------------------------------------------
  | Playwright MCP Configuration
  |--------------------------------------------------------------------------
  |
  | Configuration for Playwright MCP server integration with Laravel Boost.
  | This allows for browser automation, E2E testing, and UI validation.
  |
  */

  'enabled' => env('PLAYWRIGHT_MCP_ENABLED', false),

  'base_url' => env('PLAYWRIGHT_MCP_BASE_URL', 'http://localhost:3000'),

  'timeout' => env('PLAYWRIGHT_MCP_TIMEOUT', 30),

  /*
  |--------------------------------------------------------------------------
  | Default Browser Settings
  |--------------------------------------------------------------------------
  |
  | Default browser configuration for Playwright MCP operations.
  |
  */

  'browser' => [
    'type' => env('PLAYWRIGHT_BROWSER_TYPE', 'chromium'),
    'headless' => env('PLAYWRIGHT_BROWSER_HEADLESS', true),
    'viewport' => [
      'width' => env('PLAYWRIGHT_VIEWPORT_WIDTH', 1280),
      'height' => env('PLAYWRIGHT_VIEWPORT_HEIGHT', 720),
    ],
  ],

  /*
  |--------------------------------------------------------------------------
  | Test Configuration
  |--------------------------------------------------------------------------
  |
  | Configuration for automated testing scenarios.
  |
  */

  'testing' => [
    'screenshot_path' => storage_path('app/playwright/screenshots'),
    'trace_path' => storage_path('app/playwright/traces'),
    'video_path' => storage_path('app/playwright/videos'),
    'save_screenshots' => env('PLAYWRIGHT_SAVE_SCREENSHOTS', true),
    'save_traces' => env('PLAYWRIGHT_SAVE_TRACES', true),
    'save_videos' => env('PLAYWRIGHT_SAVE_VIDEOS', false),
  ],

  /*
  |--------------------------------------------------------------------------
  | Integration with Laravel Boost
  |--------------------------------------------------------------------------
  |
  | Configuration for integrating Playwright MCP with Laravel Boost features.
  |
  */

  'boost_integration' => [
    'enabled' => env('PLAYWRIGHT_BOOST_INTEGRATION', true),
    'log_browser_errors' => env('PLAYWRIGHT_LOG_BROWSER_ERRORS', true),
    'sync_with_boost_logs' => env('PLAYWRIGHT_SYNC_WITH_BOOST_LOGS', true),
  ],

  /*
  |--------------------------------------------------------------------------
  | Application Testing Scenarios
  |--------------------------------------------------------------------------
  |
  | Predefined testing scenarios for the CheQQme Data Center application.
  |
  */

  'scenarios' => [
    'filament_login' => [
      'url' => '/admin/login',
      'tests' => [
        'login_form_present' => 'form[action*="login"]',
        'email_input_present' => 'input[type="email"]',
        'password_input_present' => 'input[type="password"]',
        'submit_button_present' => 'button[type="submit"]',
      ],
    ],
    'action_board' => [
      'url' => '/admin/action-board',
      'tests' => [
        'kanban_board_present' => '[data-flowforge-kanban]',
        'columns_present' => '[data-flowforge-column]',
        'task_cards_present' => '[data-flowforge-card]',
      ],
    ],
    'api_endpoints' => [
      'users' => '/api/users',
      'tasks' => '/api/tasks',
      'projects' => '/api/projects',
      'clients' => '/api/clients',
    ],
  ],

  /*
  |--------------------------------------------------------------------------
  | Security Configuration
  |--------------------------------------------------------------------------
  |
  | Security settings for Playwright MCP operations.
  |
  */

  'security' => [
    'allowed_domains' => env('PLAYWRIGHT_ALLOWED_DOMAINS', 'localhost,127.0.0.1'),
    'block_external_requests' => env('PLAYWRIGHT_BLOCK_EXTERNAL', true),
    'ignore_https_errors' => env('PLAYWRIGHT_IGNORE_HTTPS_ERRORS', false),
  ],
];


