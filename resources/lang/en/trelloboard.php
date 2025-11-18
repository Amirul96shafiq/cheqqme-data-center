<?php

return [
    'navigation_group' => 'Resources',
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
        'show_on_boards' => 'Show on Boards',
        'hide_from_boards' => 'Hide from Boards',
        'toggle_show_on_boards' => 'Toggle Show on Boards',
        'toggle_show_on_boards_modal_heading' => 'Toggle Show on Boards',
        'toggle_show_on_boards_modal_description' => 'This will toggle the "Show on Boards" status for the selected records.',
        'toggle_show_on_boards_modal_confirm' => 'Toggle Status',
        'status_updated' => 'Status updated successfully',
        'refresh_sidebar_notification' => 'Please refresh the page to see the updated Trello boards in the sidebar.',
        'make_draft' => 'Change to Draft',
        'make_active' => 'Change to Active',
        'make_draft_tooltip' => 'Change this board to Draft status (only you can see it)',
        'make_active_tooltip' => 'Change this board to Active status (all users can see it)',
        'board_activated' => 'Board is now Active and visible to all users.',
        'board_made_draft' => 'Board is now in Draft mode and only visible to you.',
    ],

    'section' => [
        'trello_board_info' => 'Trello Board Information',
        'display_info' => 'Visibility Resource Information',
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
        'status' => 'Visibility Status',
        'status_active' => 'Active',
        'status_draft' => 'Draft',
        'status_helper' => 'Active boards are visible to all users. Draft boards are only visible to you.',
        'status_helper_readonly' => 'Only the user who created this resource can change the visibility status. Please contact',
        'notes_helper' => 'Remaining characters: :count',
        'notes_warning' => 'Notes must not exceed 500 visible characters.',
        'extra_information' => 'Extra Information',
        'extra_title' => 'Title',
        'extra_value' => 'Value',
        'add_extra_info' => '+ Add Extra Information',
        'title_placeholder_short' => 'Title goes here',
        'board_name_helper' => 'Extracts from the Board URL (.../board-name)',
    ],

    'table' => [
        'id' => 'ID',
        'board_name' => 'Board Name',
        'board_url' => 'Board URL',
        'show_on_boards' => 'Show on Boards',
        'status' => 'Visibility',
        'status_active' => 'Active',
        'status_draft' => 'Draft',
        'created_at_by' => 'Created At (By)',
        'updated_at_by' => 'Updated At (by)',
        'show_on_boards_true' => 'Yes',
        'show_on_boards_false' => 'No',
    ],

    'search' => [
        'board_url' => 'Board URL',
        'show_on_board' => 'Show on Board',
        'show_on_board_true' => 'Yes',
        'show_on_board_false' => 'No',
    ],

    'filter' => [
        'trashed' => 'Trashed',
    ],
];
