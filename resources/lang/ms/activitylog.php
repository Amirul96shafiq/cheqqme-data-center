<?php

return [
  'navigation_group' => 'Alat',
  'navigation_label' => 'Log Aktiviti',

  'labels' => [
    'singular' => 'Log Aktiviti',
    'plural' => 'Log-Log Aktiviti',
  ],

  'forms' => [
    'changes' => 'Perubahan',
    'fields' => [
      'causer' => ['label' => 'Pengguna'],
      'subject_type' => ['label' => 'Subjek'],
      'description' => ['label' => 'Deskripsi'],
      'log_name' => ['label' => 'Jenis Log'],
      'event' => ['label' => 'Acara'],
      'created_at' => ['label' => 'Direkodkan Pada'],
      'properties' => ['label' => 'Maklumat Tambahan'],
      'old' => ['label' => 'Data Lama'],
      'attributes' => ['label' => 'Data Baharu'],
    ],
  ],

  'tables' => [
    'columns' => [
      'log_name' => ['label' => 'Jenis Log'],
      'event' => ['label' => 'Acara'],
      'subject_type' => [
        'label' => 'Subjek',
        'soft_deleted' => ' (Telah Dihapus Sementara)',
        'deleted' => ' (Subjek Dihapus)',
      ],
      'causer' => ['label' => 'Pengguna'],
      'properties' => ['label' => 'Maklumat Tambahan'],
      'created_at' => ['label' => 'Direkodkan Pada'],
    ],
    'filters' => [
      'created_at' => [
        'label' => 'Tarikh Dicipta',
        'created_from_indicator' => 'Dari :created_from',
        'created_until_indicator' => 'Hingga :created_until',
      ],
      'event' => ['label' => 'Acara'],
    ],
  ],

  'action' => [
    'restore' => 'Pulihkan',
    'edit' => 'Lihat',
    'restore_soft_delete' => [
      'label' => 'Pulihkan (Hapus Sementara)',
      'modal_heading' => 'Sahkan Pemulihan',
      'modal_description' => 'Adakah anda pasti mahu memulihkan item ini daripada penghapusan sementara?',
    ],
    'event' => [
      'created' => 'Dicipta',
      'updated' => 'Dikemaskini',
      'deleted' => 'Dihapus',
      'restored' => 'Dipulihkan',
      'draft' => 'Deraf',
    ],
  ],

  'notifications' => [
    'activity_not_found' => 'Log aktiviti tidak dijumpai.',
    'no_properties_to_restore' => 'Tiada data untuk dipulihkan.',
    'subject_not_found' => 'Subjek tidak dijumpai.',
    'activity_restored_successfully' => 'Aktiviti berjaya dipulihkan.',
    'record_not_found' => 'Rekod tidak dijumpai.',
    'failed_to_restore_activity' => 'Gagal memulihkan aktiviti: :error',
    'unable_to_restore_this_model' => 'Model ini tidak boleh dipulihkan.',
    'model_successfully_restored' => 'Model berjaya dipulihkan.',
    'error_restoring_model' => 'Ralat semasa memulihkan model.',
  ],
];
