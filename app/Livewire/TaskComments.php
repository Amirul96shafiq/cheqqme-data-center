<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Task;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskComments extends Component implements HasForms
{
    use AuthorizesRequests;
    use InteractsWithForms;

    public Task $task;

    public string $newComment = '';

    // Separate form state arrays for Filament multi-form usage
    public ?array $composerData = [];

    public ?array $editData = [];

    public ?array $editReplyData = [];

    public ?array $replyData = [];

    // Editing state
    public ?int $editingId = null;

    public string $editingText = '';

    // Reply editing state
    public ?int $editingReplyId = null;

    public string $editingReplyText = '';

    // Reply state
    public ?int $replyingToId = null;

    public string $replyText = '';

    public int $visibleCount = 5; // number of comments to display initially / currently

    public bool $isLoadingMore = false; // loading state for show more button

    public ?int $cachedTotalComments = null; // cache total comments count to avoid repeated queries

    // Track mentions selected from dropdown to avoid relying only on text parsing
    public array $pendingMentionUserIds = [];

    // Track which comments have their replies expanded
    public array $expandedReplies = [];

    // Timestamp to force re-render
    public int $lastRefresh = 0;

    // Deep-link focused comment handling
    public ?int $focusCommentId = null;

    public ?int $focusParentId = null;

    #[On('refreshTaskComments')]
    public function refresh(): void
    {
        // Force Livewire to re-render by updating timestamp
        $this->lastRefresh = time();
    }

    // Handle emoji reaction notifications
    public function onEmojiReactionNotification(?array $payload = null): void
    {
        if ($payload && isset($payload['message']) && isset($payload['type'])) {
            Notification::make()
                ->title($payload['message'])
                ->{$payload['type']}()
                ->send();
        }
    }

    // Handle mention selected
    #[On('mentionSelected')]
    public function onMentionSelected(?array $payload = null): void
    {
        if (! $payload) {
            return;
        }

        $userId = $payload['userId'] ?? 0;

        // Handle special @Everyone case
        if ($userId === '@Everyone') {
            $this->pendingMentionUserIds[] = '@Everyone';

            return;
        }

        // Handle regular user IDs
        $userId = (int) $userId;
        if ($userId > 0 && ! in_array($userId, $this->pendingMentionUserIds, true)) {
            $this->pendingMentionUserIds[] = $userId;
        }
    }

    // Mount the component
    public function mount(int $taskId): void
    {
        $this->task = Task::findOrFail($taskId);
        // Ensure base form array keys exist before Filament/Livewire entangle
        $this->composerData = $this->composerData ?? [];
        if (! array_key_exists('newComment', $this->composerData)) {
            $this->composerData['newComment'] = '';
        }
        $this->editData = $this->editData ?? [];
        if (! array_key_exists('editingText', $this->editData)) {
            $this->editData['editingText'] = '';
        }
        $this->replyData = $this->replyData ?? [];
        if (! array_key_exists('replyText', $this->replyData)) {
            $this->replyData['replyText'] = '';
        }
        if (method_exists($this, 'composerForm')) {
            $this->composerForm->fill(['newComment' => '']);
        }

        // Handle deep links: ensure focused comment is loaded and replies expanded
        $focusId = (int) request()->get('focus_comment', 0);
        if ($focusId > 0) {
            $this->focusCommentId = $focusId;

            $comment = $this->task->comments()->find($focusId);
            if ($comment) {
                $targetTopLevelId = $comment->parent_id ?: $comment->id;
                $this->focusParentId = $comment->parent_id ?: null;

                if ($comment->parent_id && ! in_array($comment->parent_id, $this->expandedReplies, true)) {
                    $this->expandedReplies[] = $comment->parent_id;
                }

                // Determine the position of the target top-level comment in desc created_at order
                $target = $this->task->comments()->where('id', $targetTopLevelId)->whereNull('parent_id')->first();
                if ($target) {
                    $position = $this->task->comments()
                        ->whereNull('parent_id')
                        ->where(function ($q) use ($target) {
                            $q->where('created_at', '>', $target->created_at)
                                ->orWhere(function ($q2) use ($target) {
                                    $q2->where('created_at', $target->created_at)
                                        ->where('id', '>=', $target->id);
                                });
                        })
                        ->count();

                    if ($position > $this->visibleCount) {
                        $this->visibleCount = $position;
                    }
                }
            }
        }
    }

    // Add a comment
    public function addComment(): void
    {
        // Pull latest value from composer form state
        if (method_exists($this, 'composerForm')) {
            $state = $this->composerForm->getState();
            $this->newComment = $this->normalizeEditorInput($state['newComment'] ?? $this->newComment);
        }
        $this->validateOnly('newComment');

        // Check for whitespace issues BEFORE sanitization
        $preTextOnly = trim(strip_tags($this->newComment));
        if ($preTextOnly === '') {
            return;
        }

        // Get the original text content without trimming to check for leading/trailing whitespace
        $originalTextOnly = strip_tags($this->newComment);

        // Additional check: ensure comment doesn't start with whitespace
        if (preg_match('/^\s/', $originalTextOnly)) {
            Notification::make()
                ->title(__('comments.notifications.error_title', ['title' => 'Invalid Comment']))
                ->body(__('comments.notifications.starts_with_space', ['message' => 'Comment cannot start with a space or newline']))
                ->danger()
                ->send();

            return;
        }

        // Additional check: ensure comment doesn't end with whitespace
        if (preg_match('/\s$/', $originalTextOnly)) {
            Notification::make()
                ->title(__('comments.notifications.error_title', ['title' => 'Invalid Comment']))
                ->body(__('comments.notifications.ends_with_space', ['message' => 'Comment cannot end with a space or newline']))
                ->danger()
                ->send();

            return;
        }

        // Aggressively remove trailing newlines and empty elements before sanitizing
        // First remove any trailing <br> tags with whitespace
        $this->newComment = preg_replace('/<br\s*\/?>\s*$/', '', $this->newComment);

        // Remove empty paragraphs at the end (<p>&nbsp;</p> or <p></p> or <p> </p>)
        $this->newComment = preg_replace('/<p[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/p>\s*$/', '', $this->newComment);

        // Remove any <div> tags that might contain only whitespace at the end
        $this->newComment = preg_replace('/<div[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/div>\s*$/', '', $this->newComment);

        // Remove any trailing whitespace
        $this->newComment = rtrim($this->newComment);

        $sanitized = $this->sanitizeHtml($this->newComment);
        $textOnly = trim(strip_tags($sanitized));

        // Extract mentions from comment text
        $mentions = Comment::extractMentions($sanitized);

        // Merge with any user IDs selected via the dropdown tracking
        if (! empty($this->pendingMentionUserIds)) {
            // Merge all mentions (including @Everyone if present)
            $mentions = array_values(array_unique(array_merge($mentions, $this->pendingMentionUserIds)));
        }

        // Create the comment
        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => auth()->id(),
            'comment' => $sanitized,
            'mentions' => $mentions,
        ]);

        // Process mentions and send notifications
        $comment->processMentions();

        // Send notification
        Notification::make()
            ->title(__('comments.notifications.added_title'))
            ->body(Str::limit($textOnly, 120))
            ->success()
            ->send();

        // Clear the composer form
        $this->newComment = '';
        $this->pendingMentionUserIds = [];
        if (method_exists($this, 'composerForm')) {
            $this->composerForm->fill(['newComment' => '']);
        }
        $this->composerData['newComment'] = '';
        // Invalidate cached total count
        $this->cachedTotalComments = null;
        // keep visibleCount stable (newest-first list already includes new comment)
        $this->dispatch('refreshTaskComments');
        // Browser event to forcibly clear editor DOM (fallback)
        $this->dispatch('resetComposerEditor');
        // Dispatch event to hide any error messages
        $this->dispatch('comment-added');
    }

    // Start editing a comment
    public function startEdit(int $commentId): void
    {
        $comment = $this->task->comments()->where('status', '!=', 'deleted')->findOrFail($commentId);
        if ($comment->user_id !== auth()->id()) {
            return;
        }
        $this->editingId = $comment->id;
        $this->editingText = $comment->comment;
        // Ensure underlying form state array has key
        $this->editData = $this->editData ?? [];
        $this->editData['editingText'] = $this->editingText;
        if (method_exists($this, 'editForm')) {
            $this->editForm->fill(['editingText' => $this->editingText]);
        }
    }

    // Cancel editing a comment
    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editingText = '';
    }

    // Start editing a reply
    public function startEditReply(int $replyId): void
    {
        $reply = $this->task->comments()->where('status', '!=', 'deleted')->findOrFail($replyId);
        if ($reply->user_id !== auth()->id()) {
            return;
        }
        $this->editingReplyId = $reply->id;
        $this->editingReplyText = $reply->comment;
        // Ensure underlying form state array has key
        $this->editReplyData = $this->editReplyData ?? [];
        $this->editReplyData['editingReplyText'] = $this->editingReplyText;
        if (method_exists($this, 'editReplyForm')) {
            $this->editReplyForm->fill(['editingReplyText' => $this->editingReplyText]);
        }
    }

    // Cancel editing a reply
    public function cancelEditReply(): void
    {
        $this->editingReplyId = null;
        $this->editingReplyText = '';
    }

    // Save edited reply
    public function saveEditReply(): void
    {
        if (! $this->editingReplyId) {
            return;
        }

        $reply = $this->task->comments()->findOrFail($this->editingReplyId);
        if ($reply->user_id !== auth()->id()) {
            return;
        }

        // Pull latest value from edit reply form state
        if (method_exists($this, 'editReplyForm')) {
            $state = $this->editReplyForm->getState();
            $this->editingReplyText = $state['editingReplyText'] ?? $this->editingReplyText;
        } else {
            // Fallback: use editReplyData directly
            $this->editingReplyText = $this->editReplyData['editingReplyText'] ?? $this->editingReplyText;
        }

        $this->validateOnly('editingReplyText');

        // Get the original text content without trimming to check for leading/trailing whitespace
        $originalTextOnly = strip_tags($this->editingReplyText);

        // Additional check: ensure reply doesn't start with whitespace
        if (preg_match('/^\s/', $originalTextOnly)) {
            Notification::make()
                ->title(__('comments.notifications.error_title', ['title' => 'Invalid Reply']))
                ->body(__('comments.notifications.starts_with_space', ['message' => 'Reply cannot start with a space or newline']))
                ->danger()
                ->send();

            return;
        }

        // Additional check: ensure reply doesn't end with whitespace
        if (preg_match('/\s$/', $originalTextOnly)) {
            Notification::make()
                ->title(__('comments.notifications.error_title', ['title' => 'Invalid Reply']))
                ->body(__('comments.notifications.ends_with_space', ['message' => 'Reply cannot end with a space or newline']))
                ->danger()
                ->send();

            return;
        }

        // Aggressively remove trailing newlines and empty elements before sanitizing
        // First remove any trailing <br> tags with whitespace
        $this->editingReplyText = preg_replace('/<br\s*\/?>\s*$/', '', $this->editingReplyText);

        // Remove empty paragraphs at the end (<p>&nbsp;</p> or <p></p> or <p> </p>)
        $this->editingReplyText = preg_replace('/<p[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/p>\s*$/', '', $this->editingReplyText);

        // Remove any <div> tags that might contain only whitespace at the end
        $this->editingReplyText = preg_replace('/<div[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/div>\s*$/', '', $this->editingReplyText);

        // Remove any trailing whitespace
        $this->editingReplyText = rtrim($this->editingReplyText);

        // Use the same sanitization as saveEdit for consistency
        $sanitized = $this->sanitizeHtml($this->editingReplyText);
        $original = $reply->comment;

        // Check if content is the same as original
        if ($sanitized === $original) {
            // No change; show error message and prevent submission
            Notification::make()
                ->title(__('comments.notifications.error_title', ['title' => __('comments.notifications.duplicate_content_title')]))
                ->body(__('comments.notifications.duplicate_content'))
                ->danger()
                ->send();

            return;
        }

        // Extract mentions from the sanitized text
        $mentions = Comment::extractMentions($sanitized);

        $reply->update([
            'comment' => $sanitized,
            'mentions' => $mentions,
        ]);

        // Process mentions for notifications
        $reply->processMentions();

        // Send success notification
        $plain = trim(strip_tags($sanitized));
        Notification::make()
            ->title(__('comments.notifications.updated_title'))
            ->body(Str::limit($plain, 120))
            ->success()
            ->send();

        // Refresh the comment to ensure UI updates
        $this->dispatch('refreshTaskComments');

        $this->editingReplyId = null;
        $this->editingReplyText = '';
    }

    // Confirm delete reply
    public function confirmDeleteReply(int $replyId): void
    {
        $reply = $this->task->comments()->where('status', '!=', 'deleted')->findOrFail($replyId);
        if ($reply->user_id !== auth()->id()) {
            return;
        }

        // Use global modal instead of local state
        $this->dispatch('showGlobalModal', type: 'deleteReply', id: $replyId);
    }

    // Delete reply
    #[On('deleteReply')]
    public function deleteReply(int $replyId): void
    {
        $reply = $this->task->comments()->findOrFail($replyId);
        if ($reply->user_id !== auth()->id()) {
            return;
        }

        $this->deleteComment($replyId);
    }

    // Start replying to a comment
    public function startReply(int $commentId): void
    {
        $comment = $this->task->comments()->where('status', '!=', 'deleted')->findOrFail($commentId);
        $this->replyingToId = $comment->id;
        $this->replyText = '';
        // Ensure underlying form state array has key
        $this->replyData = $this->replyData ?? [];
        $this->replyData['replyText'] = $this->replyText;
        if (method_exists($this, 'replyForm')) {
            $this->replyForm->fill(['replyText' => $this->replyText]);
        }
    }

    // Cancel replying to a comment
    public function cancelReply(): void
    {
        $this->replyingToId = null;
        $this->replyText = '';
    }

    // Add a reply
    public function addReply(): void
    {
        if (! $this->replyingToId) {
            return;
        }

        // Pull latest value from reply form state
        if (method_exists($this, 'replyForm')) {
            $state = $this->replyForm->getState();
            $this->replyText = $this->normalizeEditorInput($state['replyText'] ?? $this->replyText);
        } else {
            // Fallback: use replyData directly
            // Use replyText property if replyData is empty
            if (empty($this->replyData['replyText']) && ! empty($this->replyText)) {
                $this->replyText = $this->normalizeEditorInput($this->replyText);
            } else {
                $this->replyText = $this->normalizeEditorInput($this->replyData['replyText'] ?? $this->replyText);
            }
        }

        $this->validateOnly('replyText');

        // Check for whitespace issues BEFORE sanitization
        $preTextOnly = trim(strip_tags($this->replyText));
        if ($preTextOnly === '') {
            return;
        }

        // Get the original text content without trimming to check for leading/trailing whitespace
        $originalTextOnly = strip_tags($this->replyText);

        // Additional check: ensure reply doesn't start with whitespace
        if (preg_match('/^\s/', $originalTextOnly)) {
            Notification::make()
                ->title(__('comments.notifications.error_title', ['title' => 'Invalid Reply']))
                ->body(__('comments.notifications.starts_with_space', ['message' => 'Reply cannot start with a space or newline']))
                ->danger()
                ->send();

            return;
        }

        // Additional check: ensure reply doesn't end with whitespace
        if (preg_match('/\s$/', $originalTextOnly)) {
            Notification::make()
                ->title(__('comments.notifications.error_title', ['title' => 'Invalid Reply']))
                ->body(__('comments.notifications.ends_with_space', ['message' => 'Reply cannot end with a space or newline']))
                ->danger()
                ->send();

            return;
        }

        // Aggressively remove trailing newlines and empty elements before sanitizing
        // First remove any trailing <br> tags with whitespace
        $this->replyText = preg_replace('/<br\s*\/?>\s*$/', '', $this->replyText);

        // Remove empty paragraphs at the end (<p>&nbsp;</p> or <p></p> or <p> </p>)
        $this->replyText = preg_replace('/<p[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/p>\s*$/', '', $this->replyText);

        // Remove any <div> tags that might contain only whitespace at the end
        $this->replyText = preg_replace('/<div[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/div>\s*$/', '', $this->replyText);

        // Remove any trailing whitespace
        $this->replyText = rtrim($this->replyText);

        $sanitized = $this->sanitizeHtml($this->replyText);
        $textOnly = trim(strip_tags($sanitized));

        // Extract mentions from reply text
        $mentions = Comment::extractMentions($sanitized);

        // Merge with any user IDs selected via the dropdown tracking
        if (! empty($this->pendingMentionUserIds)) {
            // Merge all mentions (including @Everyone if present)
            $mentions = array_values(array_unique(array_merge($mentions, $this->pendingMentionUserIds)));
        }

        // Create the reply
        $reply = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => auth()->id(),
            'parent_id' => $this->replyingToId,
            'comment' => $sanitized,
            'mentions' => $mentions,
        ]);

        // Process mentions and send notifications
        $reply->processMentions();

        // Auto-expand replies section when adding a new reply
        if (! in_array($this->replyingToId, $this->expandedReplies)) {
            $this->expandedReplies[] = $this->replyingToId;
        }

        // Send notification
        Notification::make()
            ->title(__('comments.notifications.reply_added_title'))
            ->body(Str::limit($textOnly, 120))
            ->success()
            ->send();

        // Clear the reply form
        $this->replyText = '';
        $this->pendingMentionUserIds = [];
        if (method_exists($this, 'replyForm')) {
            $this->replyForm->fill(['replyText' => '']);
        }
        $this->replyData['replyText'] = '';
        $this->replyingToId = null;
        // keep visibleCount stable (newest-first list already includes new reply)
        $this->dispatch('refreshTaskComments');
        // Browser event to forcibly clear editor DOM (fallback)
        $this->dispatch('resetReplyEditor');
        // Dispatch event to hide any error messages
        $this->dispatch('reply-added');
    }

    // Save editing a comment
    public function saveEdit(): void
    {
        if (! $this->editingId) {
            return;
        }
        if (method_exists($this, 'editForm')) {
            $state = $this->editForm->getState();
            $this->editingText = $this->normalizeEditorInput($state['editingText'] ?? $this->editingText);
        }
        $this->validateOnly('editingText');
        $comment = $this->task->comments()->findOrFail($this->editingId);
        if ($comment->user_id !== auth()->id()) {
            return;
        }
        $original = $comment->comment;

        // Check for whitespace issues BEFORE sanitization
        $preTextOnly = trim(strip_tags($this->editingText));
        if ($preTextOnly === '') {
            Notification::make()->title(__('comments.notifications.not_updated_title'))->body(__('comments.notifications.edited_empty'))->danger()->send();

            return;
        }

        // Get the original text content without trimming to check for leading/trailing whitespace
        $originalTextOnly = strip_tags($this->editingText);

        // Additional check: ensure comment doesn't start with whitespace
        if (preg_match('/^\s/', $originalTextOnly)) {
            Notification::make()
                ->title(__('comments.notifications.error_title', ['title' => 'Invalid Comment']))
                ->body(__('comments.notifications.starts_with_space', ['message' => 'Comment cannot start with a space or newline']))
                ->danger()
                ->send();

            return;
        }

        // Additional check: ensure comment doesn't end with whitespace
        if (preg_match('/\s$/', $originalTextOnly)) {
            Notification::make()
                ->title(__('comments.notifications.error_title', ['title' => 'Invalid Comment']))
                ->body(__('comments.notifications.ends_with_space', ['message' => 'Comment cannot end with a space or newline']))
                ->danger()
                ->send();

            return;
        }

        // Aggressively remove trailing newlines and empty elements before sanitizing
        // First remove any trailing <br> tags with whitespace
        $this->editingText = preg_replace('/<br\s*\/?>\s*$/', '', $this->editingText);

        // Remove empty paragraphs at the end (<p>&nbsp;</p> or <p></p> or <p> </p>)
        $this->editingText = preg_replace('/<p[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/p>\s*$/', '', $this->editingText);

        // Remove any <div> tags that might contain only whitespace at the end
        $this->editingText = preg_replace('/<div[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/div>\s*$/', '', $this->editingText);

        // Remove any trailing whitespace
        $this->editingText = rtrim($this->editingText);

        $sanitized = $this->sanitizeHtml($this->editingText);
        $plain = trim(strip_tags($sanitized));
        if ($sanitized === $original) {
            // No change; show error message and prevent submission
            Notification::make()
                ->title(__('comments.notifications.error_title', ['title' => __('comments.notifications.duplicate_content_title')]))
                ->body(__('comments.notifications.duplicate_content'))
                ->danger()
                ->send();

            return;
        }

        // Extract mentions from updated comment text
        $mentions = Comment::extractMentions($sanitized);
        if (! empty($this->pendingMentionUserIds)) {
            // Merge all mentions (including @Everyone if present)
            $mentions = array_values(array_unique(array_merge($mentions, $this->pendingMentionUserIds)));
        }

        // Update the comment
        $comment->update([
            'comment' => $sanitized,
            'mentions' => $mentions,
        ]);

        // Process mentions and send notifications for new mentions
        $comment->processMentions();

        // Send notification
        Notification::make()
            ->title(__('comments.notifications.updated_title'))
            ->body(Str::limit($plain, 120))
            ->success()
            ->send();
        $this->cancelEdit();
        $this->pendingMentionUserIds = [];
        $this->dispatch('refreshTaskComments');
    }

    // Update the composer data
    public function updatedComposerData($value, $key): void
    {
        if ($key === 'newComment') {
            $this->newComment = $this->normalizeEditorInput($value);
            if (method_exists($this, 'composerForm')) {
                $this->composerForm->fill(['newComment' => $this->newComment]);
            }
        }
    }

    // Update the edit data
    public function updatedEditData($value, $key): void
    {
        if ($key === 'editingText') {
            $this->editingText = $this->normalizeEditorInput($value);
            if (method_exists($this, 'editForm')) {
                $this->editForm->fill(['editingText' => $this->editingText]);
            }
        }
    }

    // Update the reply data
    public function updatedReplyData($value): void
    {
        if (is_array($value) && isset($value['replyText'])) {
            $this->replyText = $this->normalizeEditorInput($value['replyText']);
            if (method_exists($this, 'replyForm')) {
                $this->replyForm->fill(['replyText' => $this->replyText]);
            }
        }
    }

    public function updatedEditingReplyText($value): void
    {
        // Handle direct updates to editingReplyText property
        $this->editingReplyText = $this->normalizeEditorInput($value);

        // Update the form data to keep it synchronized
        $this->editReplyData = $this->editReplyData ?? [];
        $this->editReplyData['editingReplyText'] = $this->editingReplyText;

        if (method_exists($this, 'editReplyForm')) {
            $this->editReplyForm->fill(['editingReplyText' => $this->editingReplyText]);
        }
    }

    public function updatedEditReplyData($value, $key): void
    {
        // Handle both array and direct string updates
        if (is_array($value) && isset($value['editingReplyText'])) {
            // Array format: editReplyData = ['editingReplyText' => 'value']
            $this->editingReplyText = $this->normalizeEditorInput($value['editingReplyText']);
        } elseif (is_string($value) && $key === 'editingReplyText') {
            // Direct format: editReplyData.editingReplyText = 'value'
            $this->editingReplyText = $this->normalizeEditorInput($value);
        }

        if (method_exists($this, 'editReplyForm')) {
            $this->editReplyForm->fill(['editingReplyText' => $this->editingReplyText]);
        }
    }

    // Delete a comment
    public function deleteComment(int $commentId): void
    {
        $comment = $this->task->comments()->findOrFail($commentId);
        if ($comment->user_id !== auth()->id()) {
            return;
        }

        // Mark as deleted instead of soft deleting
        $comment->status = 'deleted';
        $comment->deleted_at = now();
        $comment->save();

        // Send notification
        Notification::make()
            ->title(__('comments.notifications.deleted_title'))
            ->body(Str::limit($comment->comment, 120))
            ->warning()
            ->send();

        // Only adjust visibleCount for main comments (not replies)
        if (is_null($comment->parent_id)) {
            // Invalidate cached total count
            $this->cachedTotalComments = null;
            $total = $this->getTotalCommentsProperty();
            if ($this->visibleCount > $total) {
                $this->visibleCount = $total;
            }
        }

        $this->dispatch('refreshTaskComments');
    }

    // Confirm deleting a comment
    public function confirmDelete(int $commentId): void
    {
        $comment = $this->task->comments()->where('status', '!=', 'deleted')->findOrFail($commentId);
        if ($comment->user_id !== auth()->id()) {
            return;
        }

        // Use global modal instead of local state
        $this->dispatch('showGlobalModal', type: 'deleteComment', id: $commentId);
    }

    // Perform deleting a comment
    #[On('performDelete')]
    public function performDelete(int $commentId): void
    {
        $comment = $this->task->comments()->findOrFail($commentId);
        if ($comment->user_id !== auth()->id()) {
            return;
        }

        $this->deleteComment($commentId);
    }

    // Get the comments
    public function getCommentsProperty()
    {
        return $this->task->comments()
            ->where('status', '!=', 'deleted')
            ->whereNull('parent_id') // Only top-level comments
            ->with([
                // Ensure modal has full user info (email, country, timezone, cover_image, online_status, spotify_id)
                'user:id,name,username,avatar,email,timezone,country,cover_image,online_status,spotify_id',
                'reactions.user:id,name,username,avatar,email,timezone,country,cover_image,online_status,spotify_id',
                'replies' => function ($query) {
                    $query->where('status', '!=', 'deleted')
                        ->with([
                            'user:id,name,username,avatar,email,timezone,country,cover_image,online_status,spotify_id',
                            'reactions.user:id,name,username,avatar,email,timezone,country,cover_image,online_status,spotify_id',
                        ]);
                },
            ])
            ->orderByDesc('created_at')
            ->take($this->visibleCount)
            ->get();
    }

    // Get the total comments
    public function getTotalCommentsProperty(): int
    {
        if ($this->cachedTotalComments === null) {
            $this->cachedTotalComments = $this->task->comments()
                ->where('status', '!=', 'deleted')
                ->whereNull('parent_id')
                ->count();
        }

        return $this->cachedTotalComments;
    }

    // Show more comments
    public function showMore(): void
    {
        $total = $this->getTotalCommentsProperty();
        $remaining = $total - $this->visibleCount;

        if ($remaining <= 0) {
            return;
        }

        $this->visibleCount += min(5, $remaining);
        $this->dispatch('comments-show-more');
    }

    // Render the component
    public function render()
    {
        // Final pre-render safeguard: clear any accidental 'undefined' before view output
        if (method_exists($this, 'composerForm')) {
            $state = $this->composerForm->getState();
            if (isset($state['newComment']) && is_string($state['newComment']) && Str::lower(trim(strip_tags($state['newComment']))) === 'undefined') {
                $this->composerForm->fill(['newComment' => '']);
                $this->newComment = '';
            }
        }

        return view('livewire.task-comments');
    }

    // Hydrate the component
    public function hydrate(): void
    {
        // Defensive: clear any literal 'undefined' string that may slip into state before rendering
        if (is_string($this->newComment) && Str::lower(trim(strip_tags($this->newComment))) === 'undefined') {
            $this->newComment = '';
        }
        if (method_exists($this, 'composerForm')) {
            $state = $this->composerForm->getState();
            if (isset($state['newComment']) && is_string($state['newComment']) && Str::lower(trim(strip_tags($state['newComment']))) === 'undefined') {
                $this->composerForm->fill(['newComment' => '']);
            }
            // Keep backing array in sync for entangle path reliability
            $this->composerData['newComment'] = $state['newComment'] ?? '';
        }
    }

    // Get the rules
    protected function rules(): array
    {
        return [
            'newComment' => 'required|string|max:1000',
            'editingText' => 'required|string|max:1000',
            'editingReplyText' => 'required|string|max:1000',
            'replyText' => 'required|string|max:1000',
        ];
    }

    // Get the forms
    protected function getForms(): array
    {
        return [
            // Composer form
            'composerForm' => $this->makeForm()->schema([
                RichEditor::make('newComment')
                    ->label('')
                    ->placeholder(__('comments.composer.placeholder'))
                    ->toolbarButtons(['bold', 'italic', 'strike', 'bulletList', 'orderedList', 'link', 'codeBlock'])
                    ->extraAttributes(['class' => 'minimal-comment-editor'])
                    ->maxLength(200)
                    ->extraInputAttributes(['style' => 'min-height:6rem;max-height:29rem;overflow-y:auto;resize:vertical;'])
                    ->default('')
                    ->formatStateUsing(function ($state) {
                        if (is_string($state) && Str::lower(trim(strip_tags($state))) === 'undefined') {
                            return '';
                        }

                        return $state;
                    })
                    ->mutateDehydratedStateUsing(function (?string $state) {
                        if (is_string($state) && Str::lower(trim(strip_tags($state))) === 'undefined') {
                            return '';
                        }

                        return $state;
                    })
                    ->columnSpanFull(),
            ])->statePath('composerData'),
            // Edit form
            'editForm' => $this->makeForm()->schema([
                RichEditor::make('editingText')
                    ->label('')
                    ->placeholder(__('comments.composer.edit_placeholder'))
                    ->toolbarButtons(['bold', 'italic', 'strike', 'bulletList', 'orderedList', 'link', 'codeBlock'])
                    ->extraAttributes(['class' => 'minimal-comment-editor'])
                    ->maxLength(200)
                    ->extraInputAttributes(['style' => 'min-height:6rem;max-height:15rem;overflow-y:auto;resize:vertical;'])
                    ->default('')
                    ->formatStateUsing(function ($state) {
                        if (is_string($state) && Str::lower(trim(strip_tags($state))) === 'undefined') {
                            return '';
                        }

                        return $state;
                    })
                    ->mutateDehydratedStateUsing(function (?string $state) {
                        if (is_string($state) && Str::lower(trim(strip_tags($state))) === 'undefined') {
                            return '';
                        }

                        return $state;
                    })
                    ->columnSpanFull(),
            ])->statePath('editData'),
            // Reply form
            'replyForm' => $this->makeForm()->schema([
                RichEditor::make('replyText')
                    ->label('')
                    ->placeholder(__('comments.composer.reply_placeholder'))
                    ->toolbarButtons(['bold', 'italic', 'strike', 'bulletList', 'orderedList', 'link', 'codeBlock'])
                    ->extraAttributes(['class' => 'minimal-comment-editor'])
                    ->maxLength(200)
                    ->extraInputAttributes(['style' => 'min-height:6rem;max-height:15rem;overflow-y:auto;resize:vertical;'])
                    ->default('')
                    ->formatStateUsing(function ($state) {
                        if (is_string($state) && Str::lower(trim(strip_tags($state))) === 'undefined') {
                            return '';
                        }

                        return $state;
                    })
                    ->mutateDehydratedStateUsing(function (?string $state) {
                        if (is_string($state) && Str::lower(trim(strip_tags($state))) === 'undefined') {
                            return '';
                        }

                        return $state;
                    })
                    ->columnSpanFull(),
            ])->statePath('replyData'),
            // Edit reply form
            'editReplyForm' => $this->makeForm()->schema([
                RichEditor::make('editingReplyText')
                    ->label('')
                    ->placeholder(__('comments.composer.edit_placeholder'))
                    ->toolbarButtons(['bold', 'italic', 'strike', 'bulletList', 'orderedList', 'link', 'codeBlock'])
                    ->extraAttributes(['class' => 'minimal-comment-editor'])
                    ->maxLength(200)
                    ->extraInputAttributes(['style' => 'min-height:6rem;max-height:15rem;overflow-y:auto;resize:vertical;'])
                    ->default('')
                    ->formatStateUsing(function ($state) {
                        if (is_string($state) && Str::lower(trim(strip_tags($state))) === 'undefined') {
                            return '';
                        }

                        return $state;
                    })
                    ->mutateDehydratedStateUsing(function (?string $state) {
                        if (is_string($state) && Str::lower(trim(strip_tags($state))) === 'undefined') {
                            return '';
                        }

                        return $state;
                    })
                    ->columnSpanFull(),
            ])->statePath('editReplyData'),
        ];
    }

    // Sanitize the HTML
    private function sanitizeHtml(?string $html): string
    {
        $html = $html ?? '';

        // 1) Remove script/style entirely
        $html = preg_replace('/<(script|style)[^>]*?>.*?<\/\1>/is', '', $html);

        // 2) Normalize legacy tags to semantic ones
        $html = preg_replace('/<b\b[^>]*>/i', '<strong>', $html);
        $html = preg_replace('/<\/b>/i', '</strong>', $html);
        $html = preg_replace('/<i\b[^>]*>/i', '<em>', $html);
        $html = preg_replace('/<\/i>/i', '</em>', $html);
        $html = preg_replace_callback('/<\/?strike>/i', function ($m) {
            return str_starts_with($m[0], '</') ? '</s>' : '<s>';
        }, $html);

        // 3) Convert non-breaking spaces
        $html = str_replace('&nbsp;', ' ', $html);

        // 4) Whitelist only the tags supported by the current RichEditor toolbar
        $allowed = '<strong><em><s><code><pre><a><ul><ol><li><br><p>';
        $html = strip_tags($html, $allowed);

        // 5) Sanitize anchor tags (allow only safe href + standard attrs)
        if (stripos($html, '<a') !== false) {
            $html = preg_replace_callback('/<a\s+([^>]+)>/i', function ($m) {
                $attr = $m[1];
                $href = '';
                if (preg_match('/href\s*=\s*"([^"]*)"/i', $attr, $hrefMatch)) {
                    $href = $hrefMatch[1];
                } elseif (preg_match("/href\s*=\s*'([^']*)'/i", $attr, $hrefMatch)) {
                    $href = $hrefMatch[1];
                }
                if ($href && ! preg_match('/^https?:\/\//i', $href)) {
                    $href = 'https://'.ltrim($href);
                }
                $safe = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');

                return '<a href="'.$safe.'" target="_blank" rel="nofollow noopener">';
            }, $html);
            // Drop event handlers / javascript: remnants just in case
            $html = preg_replace('/<a([^>]*)(on[a-z]+\s*=\s*"[^"]*")([^>]*)>/i', '<a$1$3>', $html);
            $html = preg_replace('/<a([^>]*)(javascript:)[^>]*>/i', '<a$1>', $html);
        }

        // 6) Strip attributes from all other allowed tags
        $html = preg_replace_callback('/<(?!a\b)(strong|em|s|code|pre|ul|ol|li|br|p)([^>]*)>/i', function ($m) {
            return '<'.strtolower($m[1]).'>';
        }, $html);

        // 7) Collapse excessive <br>
        $html = preg_replace('/(<br\s*\/?>(\s|&nbsp;)*?){3,}/i', '<br><br>', $html);

        // 8) Remove empty paragraphs
        $html = preg_replace('/<p[^>]*>\s*<\/p>/i', '', $html);

        // 9) Final trim
        return trim($html);
    }

    // Normalize the editor input
    private function normalizeEditorInput(?string $value): string
    {
        $value = $value ?? '';
        $plain = trim(strip_tags($value));
        $lower = Str::lower($plain);
        if ($lower === 'undefined' || $lower === 'null' || $lower === '"undefined"') {
            return '';
        }

        // Aggressively remove trailing newlines, <br> tags, and empty paragraphs at the  front and end of content
        // First remove any trailing <br> tags with whitespace
        $value = preg_replace('/<br\s*\/?>\s*$/', '', $value);

        // Remove empty paragraphs at the front (<p>&nbsp;</p> or <p></p> or <p> </p>)
        $value = preg_replace('/^<p[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/p>\s*/', '', $value);

        // Remove empty paragraphs at the end (<p>&nbsp;</p> or <p></p> or <p> </p>)
        $value = preg_replace('/<p[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/p>\s*$/', '', $value);

        // Remove any <div> tags that might contain only whitespace at the front
        $value = preg_replace('/^<div[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/div>\s*/', '', $value);

        // Remove any <div> tags that might contain only whitespace at the end
        $value = preg_replace('/<div[^>]*>(\s|&nbsp;|<br\s*\/?>)*<\/div>\s*$/', '', $value);

        // Remove any trailing whitespace
        $value = rtrim($value);

        return $value;
    }

    // Force delete a comment (permanently remove from database)
    #[On('forceDeleteComment')]
    public function forceDeleteComment(int $commentId): void
    {
        $comment = $this->task->comments()->findOrFail($commentId);
        if ($comment->user_id !== auth()->id()) {
            return;
        }

        // Only allow force delete for deleted comments
        if ($comment->status !== 'deleted') {
            return;
        }

        // Permanently delete the comment
        $commentText = $comment->comment; // Store comment text before deletion
        $isMainComment = is_null($comment->parent_id); // Store parent_id check before deletion
        $comment->delete();

        // Send notification
        Notification::make()
            ->title(__('comments.notifications.force_deleted_title'))
            ->body(Str::limit($commentText, 120))
            ->danger()
            ->send();

        // Adjust visibleCount if it exceeds remaining comments
        if ($isMainComment) {
            // Invalidate cached total count
            $this->cachedTotalComments = null;
            $total = $this->getTotalCommentsProperty();
            if ($this->visibleCount > $total) {
                $this->visibleCount = $total;
            }
        }

        $this->dispatch('refreshTaskComments');
    }

    // Confirm force deleting a comment
    public function confirmForceDelete(int $commentId): void
    {
        $comment = $this->task->comments()->where('status', '=', 'deleted')->findOrFail($commentId);
        if ($comment->user_id !== auth()->id()) {
            return;
        }

        // Check if this is a main comment with replies
        if (is_null($comment->parent_id)) {
            $hasReplies = $this->task->comments()
                ->where('parent_id', $commentId)
                ->exists();

            if ($hasReplies) {
                // Show notification that force delete is not allowed
                Notification::make()
                    ->title(__('comments.notifications.cannot_force_delete_with_replies_title'))
                    ->body(__('comments.notifications.cannot_force_delete_with_replies_body'))
                    ->danger()
                    ->send();

                return;
            }
        }

        // Use global modal instead of local state
        $this->dispatch('showGlobalModal', type: 'forceDeleteComment', id: $commentId);
    }

    // Force delete a reply (permanently remove from database)
    #[On('forceDeleteReply')]
    public function forceDeleteReply(int $replyId): void
    {
        $reply = $this->task->comments()->findOrFail($replyId);
        if ($reply->user_id !== auth()->id()) {
            return;
        }

        // Only allow force delete for deleted replies
        if ($reply->status !== 'deleted') {
            return;
        }

        $this->forceDeleteComment($replyId);
    }

    // Confirm force deleting a reply
    public function confirmForceDeleteReply(int $replyId): void
    {
        $reply = $this->task->comments()->where('status', '=', 'deleted')->findOrFail($replyId);
        if ($reply->user_id !== auth()->id()) {
            return;
        }

        // Use global modal instead of local state
        $this->dispatch('showGlobalModal', type: 'forceDeleteReply', id: $replyId);
    }
}
