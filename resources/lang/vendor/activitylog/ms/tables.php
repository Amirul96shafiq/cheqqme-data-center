<?php

return [
    'columns' => [
        'log_name' => [
            'label' => 'Jenis',
        ],
        'event' => [
            'label' => 'Peristiwa',
        ],
        'subject_type' => [
            'label' => 'Subjek',
            'soft_deleted' => ' (Telah Dipadam Sementara)',
            'deleted' => ' (Telah Dipadam)',
        ],
        'causer' => [
            'label' => 'Pengguna',
        ],
        'properties' => [
            'label' => 'Ciri-ciri',
        ],
        'created_at' => [
            'label' => 'Direkod pada',
        ],
    ],
    'filters' => [
        'created_at' => [
            'label' => 'Direkod pada',
            'created_from' => 'Direkod dari',
            'created_from_indicator' => 'Direkod dari: :created_from',
            'created_until' => 'Direkod hingga',
            'created_until_indicator' => 'Direkod hingga: :created_until',
        ],
        'event' => [
            'label' => 'Peristiwa',
        ],
        'log_name' => [
            'label' => 'Jenis',
        ],
    ],
];
