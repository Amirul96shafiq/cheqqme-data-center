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
    'location_timezone' => 'Lokasi & Zon Masa',
    'location_timezone_description' => 'Tetapkan lokasi anda untuk data cuaca dan zon masa untuk aplikasi.',
    'timezone' => 'Zon Masa',
    'timezone_description' => 'Tetapkan zon masa anda untuk aplikasi.',
  ],

  // Form fields
  'form' => [
    // API
    'current_timezone' => 'Zon Masa Anda',
    'timezone' => 'Zon Masa Anda',
    'current_time_in_timezone' => 'Pratonton Masa & Zon Masa',
    'select_timezone_to_preview' => 'Pilih zon masa untuk melihat pratonton masa semasa.',
    'timezone_preview_description' => 'Pratonton masa semasa dalam zon masa anda.',
    'current_api_key' => 'Kunci API Anda',
    'no_api_key' => 'Tiada kunci API dijana',
    'no_timezone' => 'Tiada zon masa dipilih',
    'timezone_preview' => 'Masa Semasa dalam Zon Masa',
    'invalid_timezone' => 'Zon masa tidak sah',
    'api_key_helper' => 'Kunci API anda digunakan untuk mengesahkan permintaan ke titik akhir API.',
    'copy_api_key' => 'Salin Kunci API',
    'generate_api_key' => 'Jana Kunci API',
    'regenerate_api_key' => 'Jana Semula Kunci API',
    'delete_api_key' => 'Padam Kunci API',
    'api_key_generated' => 'Kunci API Dijana',
    'api_key_generated_body' => 'Kunci API baru anda telah berjaya dijana.',
    'api_key_regenerated' => 'Kunci API Dijana Semula',
    'api_key_regenerated_body' => 'Kunci API anda telah dijana semula. Kunci lama tidak lagi sah.',
    'api_key_deleted' => 'Kunci API Dipadam',
    'api_key_deleted_body' => 'Kunci API anda telah berjaya dipadam.',
    'api_key_copied' => 'Kunci API Disalin',
    'api_key_copied_body' => 'Kunci API anda telah berjaya disalin ke papan klip.',
    'api_key_ready' => 'Anda telah berjaya menyalin kunci API ke dalam papan klip.',
    'api_key_copying' => 'Menyalin Kunci API',
    'api_key_copying_body' => 'Kunci API sedang disalin ke dalam papan klip.',
    'api_key_copy_failed' => 'Gagal Menyalin Kunci API',
    'api_key_copy_failed_body' => 'Gagal menyalin kunci API ke dalam papan klip.',
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

    // Timezone preview content
    'system_user' => 'Pengguna Sistem',
    'current_time' => 'Masa Semasa',
    'timezone_information' => 'Maklumat Zon Masa (TZ)',
    'identifier_name' => 'Nama Pengenalan',
    'country_code' => 'Negara (Kod Negara)',
    'utc_offset' => 'Sisihan UTC (±hh:mm)',
    'abbreviation' => 'Singkatan',
    'sample_data_preview' => 'Pratonton Data Contoh',
    'id' => 'ID',
    'title' => 'Tajuk',
    'created_at' => 'Dicipta Pada',
    'updated_at' => 'Dikemaskini Pada',
    'by' => 'Oleh',
    'sample_project_alpha' => 'Projek Alpha',
    'sample_task_review' => 'Semakan Tugasan',
    'sample_meeting_notes' => 'Nota Mesyuarat',
    'unknown' => 'Tidak Diketahui',

    // Location
    'location_settings' => 'Tetapan Lokasi',
    'city' => 'Bandar',
    'country' => 'Negara',
    'latitude' => 'Latitud',
    'longitude' => 'Longitud',
    'detect_location' => 'Set Lokasi',
    'clear_location' => 'Padam Lokasi',
    'location_detection_started' => 'Pengesanan Lokasi Dimulakan',
    'location_detection_started_body' => 'Sila benarkan akses lokasi dalam pelayar anda untuk mengesan lokasi semasa anda.',
    'location_detected' => 'Lokasi Dikesan',
    'location_detected_body' => 'Lokasi anda telah dikesan: :city, :country',
    'location_detection_failed' => 'Pengesanan Lokasi Gagal',
    'location_detection_failed_body' => 'Tidak dapat mengesan lokasi anda. Sila periksa kebenaran pelayar anda atau masukkan lokasi secara manual.',
    'location_cleared' => 'Lokasi Dihapus',
    'location_cleared_body' => 'Data lokasi anda telah dihapus.',
    'location_preview' => 'Pratonton Lokasi',
    'location_preview_description' => 'Pratonton maklumat lokasi dan zon masa yang dipilih.',
    'select_location_to_preview' => 'Pilih lokasi untuk melihat pratonton.',

    // Weather Preview
    'weather_preview' => 'Pratonton Cuaca',
    'weather_preview_description' => 'Pratonton cuaca semasa untuk lokasi yang dipilih.',
    'select_location_to_preview_weather' => 'Masukkan bandar dan negara untuk melihat pratonton cuaca.',
    'weather_data_unavailable' => 'Data cuaca tidak tersedia',
    'fetching_weather' => 'Mengambil data cuaca...',
    'weather_error' => 'Tidak dapat mengambil data cuaca',

    // Actions
    'save' => 'Simpan Tetapan',
    'saved' => 'Tetapan Disimpan',
    'saved_body' => 'Tetapan anda telah berjaya disimpan.',
  ],
];
