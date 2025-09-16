<?php

return [
    // Page navigation and titles
    'page' => [
        'navigation_label' => 'Settings',
        'title' => 'Settings',
    ],

    // Actions
    'actions' => [
        'save' => 'Save changes',
    ],

    // Section headers and descriptions
    'sections' => [
        'api' => 'API Access',
        'api_description' => 'Manage your API key for external access to your data.',
        'location' => 'Location',
        'location_description' => 'Set your location for weather data and geolocation services.',
        'timezone' => 'Timezone',
        'timezone_description' => 'Set your timezone for the application.',
        'chatbot_history' => 'Chatbot History',
        'chatbot_history_description' => 'Manage your chatbot conversation backups and restore previous conversations.',
    ],

    // API-related translations
    'api' => [
        'current_key' => 'Your API Key',
        'no_key' => 'No API key generated',
        'helper' => 'Your API key is used to authenticate requests to the API endpoints.',
        'copy' => 'Copy API Key',
        'generate' => 'Generate API Key',
        'regenerate' => 'Regenerate API Key',
        'delete' => 'Delete API Key',
        'documentation' => 'API Documentation',
        'documentation_description' => 'View API endpoints, authentication methods, and usage examples.',
        'confirm_regenerate' => 'Regenerate API Key?',
        'confirm_regenerate_description' => 'This will generate a new API key and invalidate the current one. Any applications using the old key will stop working.',
        'confirm_delete' => 'Delete API Key?',
        'confirm_delete_description' => 'This will permanently delete your API key. Any applications using this key will stop working.',
        'regenerate_action' => 'Regenerate',
        'delete_action' => 'Delete',
        'documentation_content' => [
            'base_url' => 'Base URL',
            'api_header' => 'API Header',
            'authentication' => 'Authentication',
            'example_request' => 'Example Request',
            'sample_screenshot' => 'Sample Screenshot',
            'list_of_supported_api' => 'List of Supported API',
        ],
    ],

    // Location-related translations
    'location' => [
        'settings' => 'Location Settings',
        'city' => 'City',
        'country' => 'Country',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'detect' => 'Set Location',
        'clear' => 'Delete Location',
    ],

    // Timezone-related translations
    'timezone' => [
        'settings' => 'Timezone Settings',
        'current_time_preview' => 'Time & Timezone Preview',
        'select_to_preview' => 'Select a timezone to preview the current time.',
        'preview_description' => 'Preview the current time in your timezone.',
        'current_time' => 'Current Time',
        'information' => 'Timezone (TZ) Information',
        'identifier_name' => 'Identifier Name',
        'country_code' => 'Country (Country Code)',
        'utc_offset' => 'UTC Offset (±hh:mm)',
        'abbreviation' => 'Abbreviation',
        'sample_data_preview' => 'Sample Data Preview',
        'id' => 'ID',
        'title' => 'Title',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'by' => 'By',
        'sample_project_alpha' => 'Project Alpha',
        'sample_task_review' => 'Task Review',
    ],

    // Weather-related translations
    'weather' => [
        'preview' => 'Weather Preview',
        'preview_description' => 'Preview current weather for your selected location.',
        'data_unavailable' => 'Weather data unavailable',
        'error' => 'Unable to fetch weather data',
        'feels_like' => 'Feels like',
        'no_location_data_available' => 'No location data available',
    ],

    // Notification messages
    'notifications' => [
        'api_key_generated' => 'API Key Generated',
        'api_key_generated_body' => 'Your new API key has been generated successfully.',
        'api_key_regenerated' => 'API Key Regenerated',
        'api_key_regenerated_body' => 'Your API key has been regenerated. The old key is no longer valid.',
        'api_key_deleted' => 'API Key Deleted',
        'api_key_deleted_body' => 'Your API key has been deleted successfully.',
        'api_key_copied' => 'API Key Copied',
        'api_key_copied_body' => 'Your API key has been copied to clipboard successfully.',
        'api_key_copying' => 'Copying API Key',
        'api_key_copying_body' => 'Your API key is being copied to clipboard.',
        'api_key_copy_failed' => 'Failed to Copy API Key',
        'api_key_copy_failed_body' => 'Failed to copy your API key to clipboard.',
        'location_detection_started' => 'Location Detection Started',
        'location_detection_started_body' => 'Please allow location access in your browser to detect your current location.',
        'location_detected' => 'Location Detected',
        'location_detected_body' => 'Your location has been detected: :city, :country',
        'location_detection_failed' => 'Location Detection Failed',
        'location_detection_failed_body' => 'Unable to detect your location. Please check your browser permissions or enter the location manually.',
        'location_cleared' => 'Location Cleared',
        'location_cleared_body' => 'Your location data has been cleared.',
        'settings_saved' => 'Settings Saved',
        'settings_saved_body' => 'Your settings have been successfully saved.',
        'backup_created' => 'Backup Created',
        'backup_created_body' => 'Backup ":name" has been created successfully.',
        'backup_failed' => 'Backup Failed',
        'backups_refreshed' => 'Backups Refreshed',
        'backups_refreshed_body' => 'The backup list has been refreshed.',
    ],

    // Chatbot-related translations
    'chatbot' => [
        'create_backup' => 'Create Backup',
    ],
];
