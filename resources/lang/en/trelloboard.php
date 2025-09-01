<?php

return [
    'navigation_group' => 'Data Management',
    'navigation_label' => 'Trello Boards',

    'labels' => [
        'singular' => 'Trello Board',
        'plural' => 'Trello Boards',
        'edit-trello-board' => 'Edit Trello Board',
    ],

    'navigation' => [
        'labels' => 'Trello Boards',
    ],

    'actions' => [
        'create' => 'Create Trello Board',
        'delete' => 'Delete',
        'edit' => 'Edit',
        'view' => 'View',
        'restore' => 'Restore',
        'force_delete' => 'Force Delete',
        'open_url' => 'Open URL',
        'log' => 'Activity Log',
    ],

    'section' => [
        'trello_board_info' => 'Trello Board Information',
        'display_info' => 'Trello Board Display Information',
        'extra_info' => 'Trello Board Additional Information',
        'activity_logs' => 'Activity Logs',
    ],

    'form' => [
        'board_name' => 'Board Name',
        'board_url' => 'Board URL',
        'board_url_note' => 'URL for external Trello boards.',
        'board_url_helper' => 'Open URL in a new tab.',
        'open_url' => 'Open URL',
        'trelloboard_notes' => 'Notes',
        'show_on_boards' => 'Show on Boards',
        'notes_helper' => 'Remaining characters: :count',
        'notes_warning' => 'Notes must not exceed 500 visible characters.',
        'extra_information' => 'Extra Information',
        'extra_title' => 'Title',
        'extra_value' => 'Value',
        'add_extra_info' => '+ Add Extra Information',
        'title_placeholder_short' => 'Title goes here',
    ],

    'table' => [
        'id' => 'ID',
        'board_name' => 'Board Name',
        'board_url' => 'Board URL',
        'show_on_boards' => 'Show on Boards',
        'created_at' => 'Created At',
        'updated_at_by' => 'Updated At (by)',
        'show_on_boards_true' => 'Yes',
        'show_on_boards_false' => 'No',
    ],

    'search' => [
        'board_url' => 'Board URL',
    ],
];
