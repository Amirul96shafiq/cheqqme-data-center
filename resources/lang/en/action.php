<?php

return [
    'title' => 'Action Board',
    'navigation' => [
        'group' => 'Boards',
        'label' => 'Action Board',
    ],

    'card_label' => 'Action Task',
    'card_label_plural' => 'Action Tasks',

    'status' => [
        'todo' => 'To Do',
        'in_progress' => 'In Progress',
        'toreview' => 'To Review',
        'completed' => 'Completed',
        'archived' => 'Archived',
    ],

    'modal' => [
        'create_title' => 'Create Action Task',
        'edit_title' => 'Edit Action Task',
    ],

    'notifications' => [
        'created' => 'Task created successfully',
        'updated' => 'Task updated successfully',
    ],

    'form' => [
        'task_information' => 'Task Information',
        'additional_information' => 'Task Additional Information',
        'extra_information' => 'Extra Information',
        'assign_to' => 'Assign To',
        'due_date' => 'Due Date',
        'status' => 'Status',
        'description' => 'Description',
        'title' => 'Title',
        'value' => 'Value',
        'comments' => 'Comments',
        'title_placeholder' => 'Enter task title',
        'title_placeholder_short' => 'Title goes here',
        'user_with_id' => 'User #:id',
        'deleted_suffix' => ' (deleted)',
    ],

    'create' => [
        'description_helper' => 'Remaining characters: :count',
        'description_warning' => 'Description must not exceed 500 visible characters.',
        'extra_information_helper' => 'Remaining characters: :count',
        'extra_information_warning' => 'Value must not exceed 500 visible characters.',
    ],
    'edit' => [
        'description_helper' => 'Remaining characters: :count',
        'description_warning' => 'Description must not exceed 500 visible characters.',
        'extra_information_helper' => 'Remaining characters: :count',
        'extra_information_warning' => 'Value must not exceed 500 visible characters.',
    ],
];
