<?php

return [
  // Navigation
  'navigation_label' => 'Settings',
  'navigation_group' => 'User Management',

  // Page titles and descriptions
  'title' => 'Settings',
  'slug' => 'settings',

  // Sections
  'section' => [
    'general' => 'General Settings',
    'general_description' => 'Configure your general application preferences.',

    'appearance' => 'Appearance',
    'appearance_description' => 'Customize how the application looks and feels.',

    'notifications' => 'Notifications',
    'notifications_description' => 'Manage your notification preferences.',

    'privacy' => 'Privacy & Security',
    'privacy_description' => 'Control your privacy and security settings.',

    'api' => 'API Access',
    'api_description' => 'Manage your API key for external access to your data.',
  ],

  // Form fields
  'form' => [
    // General
    'timezone' => 'Timezone',
    'timezone_helper' => 'Your preferred timezone for displaying dates and times.',

    'language' => 'Language',
    'language_helper' => 'Choose your preferred language for the interface.',

    'email_notifications' => 'Email Notifications',
    'email_notifications_helper' => 'Receive notifications via email.',

    // Appearance
    'theme' => 'Theme',
    'theme_light' => 'Light',
    'theme_dark' => 'Dark',
    'theme_system' => 'System',
    'theme_helper' => 'Choose your preferred color theme.',

    'compact_mode' => 'Compact Mode',
    'compact_mode_helper' => 'Use a more compact layout to fit more content.',

    'show_sidebar' => 'Show Sidebar',
    'show_sidebar_helper' => 'Display the sidebar navigation.',

    // Notifications
    'task_notifications' => 'Task Notifications',
    'task_notifications_helper' => 'Get notified about task updates.',

    'comment_notifications' => 'Comment Notifications',
    'comment_notifications_helper' => 'Get notified about new comments.',

    'mention_notifications' => 'Mention Notifications',
    'mention_notifications_helper' => 'Get notified when you are mentioned.',

    'notification_frequency' => 'Notification Frequency',
    'frequency_immediate' => 'Immediate',
    'frequency_hourly' => 'Hourly',
    'frequency_daily' => 'Daily',
    'notification_frequency_helper' => 'How often you want to receive notifications.',

    // Privacy
    'profile_visibility' => 'Profile Visibility',
    'profile_visibility_helper' => 'Make your profile visible to other users.',

    'show_online_status' => 'Show Online Status',
    'show_online_status_helper' => 'Let others see when you are online.',

    'allow_data_export' => 'Allow Data Export',
    'allow_data_export_helper' => 'Allow the application to export your data.',

    // API
    'current_api_key' => 'Current API Key',
    'no_api_key' => 'No API key generated',
    'api_key_helper' => 'Your API key is used to authenticate requests to the API endpoints.',
    'generate_api_key' => 'Generate API Key',
    'regenerate_api_key' => 'Regenerate API Key',
    'delete_api_key' => 'Delete API Key',
    'api_key_generated' => 'API Key Generated',
    'api_key_generated_body' => 'Your new API key has been generated successfully.',
    'api_key_regenerated' => 'API Key Regenerated',
    'api_key_regenerated_body' => 'Your API key has been regenerated. The old key is no longer valid.',
    'api_key_deleted' => 'API Key Deleted',
    'api_key_deleted_body' => 'Your API key has been deleted successfully.',
    'confirm_regenerate' => 'Regenerate API Key?',
    'confirm_regenerate_description' => 'This will generate a new API key and invalidate the current one. Any applications using the old key will stop working.',
    'confirm_delete' => 'Delete API Key?',
    'confirm_delete_description' => 'This will permanently delete your API key. Any applications using this key will stop working.',
    'regenerate' => 'Regenerate',
    'delete' => 'Delete',
    'api_documentation' => 'API Documentation',
    'api_documentation_content' => '
          <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">
            <div class="space-y-4">
                <p>Base URL:</p>
                <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">:base_url</code>
                <p>API Header:</p>
                <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">Accept: application/json</code>
                <p>Authentication:</p>
                <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">Authorization: Bearer YOUR_API_KEY</code>
                <p>Example Request:</p>
                <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">GET :base_url/clients<br>Accept: application/json<br>Authorization: Bearer YOUR_API_KEY</code>
                <p>Sample Screenshot:</p>
                <img src="/images/api-sample-screenshot.png" alt="API Documentation: Sample Screenshot" class="w-full h-auto rounded-lg">
                <p>List of Supported API:</p>
                <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">GET :base_url/clients<br>GET :base_url/projects<br>GET :base_url/documents<br>GET :base_url/important-urls<br>GET :base_url/phone-numbers<br>GET :base_url/users<br>GET :base_url/comments<br>GET :base_url/comments/{comment}</code>
            </div>
          </code>
        ',

    // Actions
    'save' => 'Save Settings',
    'saved' => 'Settings Saved',
    'saved_body' => 'Your settings have been successfully saved.',
  ],
];
