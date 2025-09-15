<?php

return [
    'header' => [
        'title' => 'Comments',
    ],
    'composer' => [
        'send' => 'Submit',
        'saving' => 'Submitting...',
        'placeholder' => 'Write a comment here. Use @ to mention users.',
        'edit_placeholder' => 'Edit comment...',
    ],
    'buttons' => [
        'edit' => 'Edit',
        'delete' => 'Delete',
        'save' => 'Save changes',
        'cancel' => 'Cancel',
        'save_comment' => 'Save Comment',
    ],
    'list' => [
        'none' => 'No comments yet.',
        'none_long' => 'No comments yet. Be the first to comment!',
        'showing' => 'Showing :shown of :total comments',
        'show_more' => 'Show more (:count)',
    ],
    'meta' => [
        'unknown' => 'Unknown',
        'unknown_user' => 'Unknown User',
        'edited' => 'edited',
        'user_fallback' => 'User',
        'you' => 'You',
        'just_now' => 'Just now',
    ],
    'modal' => [
        'delete' => [
            'title' => 'Delete Comment',
            'description' => 'Are you sure you would like to delete this comment? This action can be reversed only by an admin.',
            'cancel' => 'Cancel',
            'confirm' => 'Delete',
            'close' => 'Close',
        ],
    ],
    'sidebar' => [
        'task_not_loaded' => 'Task not loaded.',
    ],
    'notifications' => [
        'added_title' => 'Comment added',
        'updated_title' => 'Comment updated',
        'deleted_title' => 'Comment deleted',
        'not_updated_title' => 'Comment not updated',
        'edited_empty' => 'Edited comment cannot be empty.',
        'error_title' => 'Error: :title',
        'starts_with_space' => 'Comment cannot start with a space or newline. Please remove any leading spaces and try again.',
        'ends_with_space' => 'Comment cannot end with a space or newline. Please remove any trailing spaces and try again.',
    ],
    'mentions' => [
        'no_users_found' => 'No users found',
        'searching' => 'Searching users...',
        'dropdown' => [
            'navigate' => 'Navigate',
            'select' => 'Select',
            'cancel' => 'Cancel',
            'notify_all' => 'Notify all users',
        ],
    ],
    'js' => [
        'textarea_missing' => 'Textarea not found',
        'enter_comment' => 'Please enter a comment',
        'task_id_missing' => 'Task ID missing',
        'security_token_missing' => 'Security token missing; refresh page.',
        'failed_save_comment' => 'Failed to save comment',
        'error_prefix' => 'Error:',
        'edit_prompt' => 'Edit your comment:',
        'error_updating_comment' => 'Error updating comment',
        'confirm_delete' => 'Are you sure you want to delete this comment?',
        'error_deleting_comment' => 'Error deleting comment',
    ],
];
