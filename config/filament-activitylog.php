<?php

return [
    'resources' => [
        'label' => 'Activity Log',
        'plural_label' => 'Activity Logs',
        'hide_restore_action' => false,
        'restore_action_label' => 'Restore',
        'hide_resource_action' => false,
        'hide_restore_model_action' => true,
        'resource_action_label' => 'View',
        'navigation_item' => true,
        'navigation_group' => null,
        'navigation_icon' => 'heroicon-o-command-line',
        'navigation_sort' => null,
        'default_sort_column' => 'id',
        'default_sort_direction' => 'desc',
        'navigation_count_badge' => false,
        'resource' => \Rmsramos\Activitylog\Resources\ActivitylogResource::class,
    ],
    'date_format' => 'j/n/y',
    'datetime_format' => 'j/n/y, h:i A',
];
