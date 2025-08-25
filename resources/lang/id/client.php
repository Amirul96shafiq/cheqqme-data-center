<?php

return [
    'navigation_group' => 'Manajemen Data',
    'navigation_label' => 'Klien',

    'labels' => [
        'singular' => 'Klien',
        'plural' => 'Klien',
    ],

    'navigation' => [
        'labels' => 'Klien',
    ],

    'actions' => [
        'create' => 'Klien Baru',
    ],

    'section' => [
        'client_info' => 'Informasi Klien',
        'company_info' => 'Informasi Perusahaan Klien',
        'extra_info' => 'Informasi Tambahan Klien',
    ],

    'form' => [
        'pic_name' => 'Nama Penanggung Jawab',
        'pic_email' => 'Email Penanggung Jawab',
        'pic_contact_number' => 'Nomor Kontak Penanggung Jawab',
        'company_name' => 'Nama Perusahaan',
        'company_email' => 'Email Perusahaan',
        'company_address' => 'Alamat Perusahaan',
        'billing_address' => 'Alamat Penagihan',
        'notes' => 'Catatan',
        'notes_helper' => 'Sisa karakter: :count',
        'notes_warning' => 'Catatan tidak boleh melebihi 500 karakter yang terlihat.',
        'company_name_helper' => 'Default ke nama penanggung jawab, bisa diubah.',
    ],

    'table' => [
        'id' => 'ID',
        'pic_name' => 'Nama Penanggung Jawab',
        'company_name' => 'Perusahaan',
        'created_at' => 'Dibuat pada',
        'updated_at_by' => 'Diperbarui pada (oleh)',
    ],
];
