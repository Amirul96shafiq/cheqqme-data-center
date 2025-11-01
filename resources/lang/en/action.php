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
        'moved_to_top' => 'Task moved to top',
        'moved_to_bottom' => 'Task moved to bottom',
        'moved_up' => 'Task moved up',
        'moved_down' => 'Task moved down',
        'move_failed' => 'Move operation failed',
        'invalid_task_id' => 'Invalid task ID provided',
    ],

    'move' => [
        'title' => 'Move Task',
        'to_column' => 'Move to Column',
        'to_top' => 'Move to Most Top',
        'to_bottom' => 'Move to Most Bottom',
        'up_one' => 'Move to Top 1 Time',
        'down_one' => 'Move to Bottom 1 Time',
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

    'search_placeholder' => 'Search tasks',
    'clear_search' => 'Clear search',
    'currently_viewing' => 'Currently viewing',
    'filter_tasks' => 'Filter tasks',
    'filters' => 'Filters',
    'reset' => 'Reset',
    'show_all' => 'Show all',
    'show_less' => 'Show less',
    'more' => 'more',

    'filter' => [
        'assigned_to' => 'Assigned To',
        'all_users' => 'All Users',
        'select_users' => 'Select users to filter by',
        'selected_users' => 'Selected Users',
        'users_selected' => 'users selected',
        'due_date' => 'Due Date',
        'due_today' => 'Due today',
        'due_this_week' => 'Due this week',
        'due_this_month' => 'Due this month',
        'due_this_year' => 'Due this year',
        'select_date_range' => 'Or select a custom date range',
        'select_due_date' => 'Select due date filter',
        'selected_due_date' => 'Selected Due Date',
        'priority' => 'Priority',
        'select_priority' => 'Select priority filter',
        'priorities_selected' => 'priorities selected',
        'selected_priority' => 'Selected Priority',
        'priority_high' => 'High Priority',
        'priority_medium' => 'Medium Priority',
        'priority_low' => 'Low Priority',
        'quick_filters' => 'Quick Filters',
        'custom_range' => 'Custom Range',
        'from_date' => 'From Date',
        'to_date' => 'To Date',
    ],

    'no_results' => [
        'title' => 'No tasks found',
        'description' => 'Try adjusting your search terms or clear the search to see all tasks.',
        'clear_button' => 'Clear search',
        'search' => [
            'title' => 'No tasks found',
            'description' => 'Try adjusting your search terms or clear the search to see all tasks.',
        ],
        'assigned_to' => [
            'title' => 'No tasks found for selected users',
            'description' => 'Try adjusting your user filter or clear the filter to see all tasks.',
        ],
        'due_date' => [
            'title' => 'No tasks found for selected date range',
            'description' => 'Try adjusting your date filter or clear the filter to see all tasks.',
        ],
        'priority' => [
            'title' => 'No tasks found for selected priority',
            'description' => 'Try adjusting your priority filter or clear the filter to see all tasks.',
        ],
    ],
];
