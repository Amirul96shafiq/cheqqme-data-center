<?php

return [
  // Navigation
  'navigation_label' => 'Tetapan',
  'navigation_group' => 'Pengurusan Pengguna',

  // Page titles and descriptions
  'title' => 'Tetapan',
  'slug' => 'tetapan',

  // Sections
  'section' => [
    'general' => 'Tetapan Umum',
    'general_description' => 'Konfigurasi pilihan aplikasi umum anda.',

    'appearance' => 'Penampilan',
    'appearance_description' => 'Sesuaikan bagaimana aplikasi kelihatan dan berasa.',

    'notifications' => 'Pemberitahuan',
    'notifications_description' => 'Urus pilihan pemberitahuan anda.',

    'privacy' => 'Privasi & Keselamatan',
    'privacy_description' => 'Kawal tetapan privasi dan keselamatan anda.',

    'api' => 'Akses API',
    'api_description' => 'Urus kunci API anda untuk akses luaran ke data anda.',
  ],

  // Form fields
  'form' => [
    // General
    'timezone' => 'Zon Masa',
    'timezone_helper' => 'Zon masa pilihan anda untuk memaparkan tarikh dan masa.',

    'language' => 'Bahasa',
    'language_helper' => 'Pilih bahasa pilihan anda untuk antara muka.',

    'email_notifications' => 'Pemberitahuan E-mel',
    'email_notifications_helper' => 'Terima pemberitahuan melalui e-mel.',

    // Appearance
    'theme' => 'Tema',
    'theme_light' => 'Terang',
    'theme_dark' => 'Gelap',
    'theme_system' => 'Sistem',
    'theme_helper' => 'Pilih tema warna pilihan anda.',

    'compact_mode' => 'Mod Padat',
    'compact_mode_helper' => 'Gunakan susun atur yang lebih padat untuk memuatkan lebih banyak kandungan.',

    'show_sidebar' => 'Tunjuk Bar Sisi',
    'show_sidebar_helper' => 'Papar navigasi bar sisi.',

    // Notifications
    'task_notifications' => 'Pemberitahuan Tugas',
    'task_notifications_helper' => 'Dapatkan pemberitahuan tentang kemas kini tugas.',

    'comment_notifications' => 'Pemberitahuan Komen',
    'comment_notifications_helper' => 'Dapatkan pemberitahuan tentang komen baru.',

    'mention_notifications' => 'Pemberitahuan Sebutan',
    'mention_notifications_helper' => 'Dapatkan pemberitahuan apabila anda disebut.',

    'notification_frequency' => 'Kekerapan Pemberitahuan',
    'frequency_immediate' => 'Segera',
    'frequency_hourly' => 'Setiap Jam',
    'frequency_daily' => 'Harian',
    'notification_frequency_helper' => 'Berapa kerap anda mahu menerima pemberitahuan.',

    // Privacy
    'profile_visibility' => 'Keterlihatan Profil',
    'profile_visibility_helper' => 'Buat profil anda kelihatan kepada pengguna lain.',

    'show_online_status' => 'Tunjuk Status Dalam Talian',
    'show_online_status_helper' => 'Biarkan orang lain melihat apabila anda dalam talian.',

    'allow_data_export' => 'Benarkan Eksport Data',
    'allow_data_export_helper' => 'Benarkan aplikasi mengeksport data anda.',

    // API
    'current_api_key' => 'Kunci API Semasa',
    'no_api_key' => 'Tiada kunci API dijana',
    'api_key_helper' => 'Kunci API anda digunakan untuk mengesahkan permintaan ke titik akhir API.',
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
