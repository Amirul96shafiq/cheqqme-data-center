<?php

return [
  // Navigation
  'navigation_label' => 'Settings',

  // Page titles and descriptions
  'title' => 'Settings',
  'slug' => 'settings',

  // Sections
  'section' => [
    'api' => 'API Access',
    'api_description' => 'Manage your API key for external access to your data.',
  ],

  // Form fields
  'form' => [
    // API
    'current_api_key' => 'Current API Key',
    'no_api_key' => 'No API key generated',
    'api_key_helper' => 'Your API key is used to authenticate requests to the API endpoints.',
    'copy_api_key' => 'Copy API Key',
    'generate_api_key' => 'Generate API Key',
    'regenerate_api_key' => 'Regenerate API Key',
    'delete_api_key' => 'Delete API Key',
    'api_key_generated' => 'API Key Generated',
    'api_key_generated_body' => 'Your new API key has been generated successfully.',
    'api_key_regenerated' => 'API Key Regenerated',
    'api_key_regenerated_body' => 'Your API key has been regenerated. The old key is no longer valid.',
    'api_key_deleted' => 'API Key Deleted',
    'api_key_deleted_body' => 'Your API key has been deleted successfully.',
    'api_key_ready' => 'You have successfully copied your API key to clipboard.',
    'api_key_copying' => 'Copying API Key',
    'api_key_copying_body' => 'Your API key is being copied to clipboard.',
    'api_key_copy_failed' => 'Failed to Copy API Key',
    'api_key_copy_failed_body' => 'Failed to copy your API key to clipboard.',
    'confirm_regenerate' => 'Regenerate API Key?',
    'confirm_regenerate_description' => 'This will generate a new API key and invalidate the current one. Any applications using the old key will stop working.',
    'confirm_delete' => 'Delete API Key?',
    'confirm_delete_description' => 'This will permanently delete your API key. Any applications using this key will stop working.',
    'regenerate' => 'Regenerate',
    'delete' => 'Delete',
    'api_documentation' => 'API Documentation',
    'api_documentation_content' => '
          <code class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">
            <div class="space-y-4">
                <p>Base URL:</p>
                <code class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">:base_url</code>
                <p>API Header:</p>
                <code class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">Accept: application/json</code>
                <p>Authentication:</p>
                <code class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">Authorization: Bearer YOUR_API_KEY</code>
                <p>Example Request:</p>
                <code class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">GET :base_url/clients<br>Accept: application/json<br>Authorization: Bearer YOUR_API_KEY</code>
                <p>Sample Screenshot:</p>
                <a href="/images/api-sample-screenshot.png" target="_blank" class="block">
                    <img src="/images/api-sample-screenshot.png" alt="API Documentation: Sample Screenshot" class="w-full h-auto rounded-lg cursor-pointer hover:opacity-90 transition-opacity">
                </a>
                <p>List of Supported API:</p>
                <code class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">GET :base_url/clients<br>GET :base_url/projects<br>GET :base_url/documents<br>GET :base_url/important-urls<br>GET :base_url/phone-numbers<br>GET :base_url/users<br>GET :base_url/comments<br>GET :base_url/comments/{comment}</code>
            </div>
          </code>
        ',

    // Actions
    'save' => 'Save Settings',
    'saved' => 'Settings Saved',
    'saved_body' => 'Your settings have been successfully saved.',
  ],
];
