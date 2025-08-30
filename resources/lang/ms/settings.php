<?php

return [
    // Navigation
    'navigation_label' => 'Tetapan',

    // Page titles and descriptions
    'title' => 'Tetapan',
    'slug' => 'tetapan',

    // Sections
    'section' => [
        'api' => 'Akses API',
        'api_description' => 'Urus kunci API anda untuk akses luaran ke data anda.',
    ],

    // Form fields
    'form' => [
        // API
        'current_api_key' => 'Kunci API Anda',
        'no_api_key' => 'Tiada kunci API dijana',
        'api_key_helper' => 'Kunci API anda digunakan untuk mengesahkan permintaan ke titik akhir API.',
        'copy_api_key' => 'Salin Kunci API',
        'api_key_ready' => 'Anda telah berjaya menyalin kunci API ke dalam papan klip.',
        'api_key_copying' => 'Menyalin Kunci API',
        'api_key_copying_body' => 'Kunci API sedang disalin ke dalam papan klip.',
        'api_key_copy_failed' => 'Gagal Menyalin Kunci API',
        'api_key_copy_failed_body' => 'Gagal menyalin kunci API ke dalam papan klip.',
        'generate_api_key' => 'Jana Kunci API',
        'regenerate_api_key' => 'Jana Semula Kunci API',
        'delete_api_key' => 'Padam Kunci API',
        'api_key_generated' => 'Kunci API Dijana',
        'api_key_generated_body' => 'Kunci API baru anda telah berjaya dijana.',
        'api_key_regenerated' => 'Kunci API Dijana Semula',
        'api_key_regenerated_body' => 'Kunci API anda telah dijana semula. Kunci lama tidak lagi sah.',
        'api_key_deleted' => 'Kunci API Dipadam',
        'api_key_deleted_body' => 'Kunci API anda telah berjaya dipadam.',
        'confirm_regenerate' => 'Jana Semula Kunci API?',
        'confirm_regenerate_description' => 'Ini akan menjana kunci API baru dan menjadikan kunci semasa tidak sah. Sebarang aplikasi yang menggunakan kunci lama akan berhenti berfungsi.',
        'confirm_delete' => 'Padam Kunci API?',
        'confirm_delete_description' => 'Ini akan memadam kunci API anda secara kekal. Sebarang aplikasi yang menggunakan kunci ini akan berhenti berfungsi.',
        'regenerate' => 'Jana Semula',
        'delete' => 'Padam',
        'api_documentation' => 'Dokumentasi API',
        'api_documentation_description' => 'Lihat titik akhir API, kaedah pengesahan, dan contoh penggunaan.',
        'api_documentation_content' => '
          <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">
            <div class="space-y-4">
                <p>URL Dasar:</p>
                <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">:base_url</code>
                <p>Header API:</p>
                <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">Accept: application/json</code>
                <p>Pengesahan:</p>
                <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">Authorization: Bearer YOUR_API_KEY</code>
                <p>Contoh Permintaan:</p>
                <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">GET :base_url/clients<br>Accept: application/json<br>Authorization: Bearer YOUR_API_KEY</code>
                <p>Contoh Screenshot:</p>
                <img src="/images/api-sample-screenshot.png" alt="API Documentation: Sample Screenshot" class="w-full h-auto rounded-lg">
                <p>Senarai API yang Disokong:</p>
                <code class="bg-gray-50 dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4 block text-gray-500 dark-text-gray-700 dark:text-gray-400">GET :base_url/clients<br>GET :base_url/projects<br>GET :base_url/documents<br>GET :base_url/important-urls<br>GET :base_url/phone-numbers<br>GET :base_url/users<br>GET :base_url/comments<br>GET :base_url/comments/{comment}</code>
            </div>
          </code>
        ',

        // Actions
        'save' => 'Simpan Tetapan',
        'saved' => 'Tetapan Disimpan',
        'saved_body' => 'Tetapan anda telah berjaya disimpan.',
    ],
];
