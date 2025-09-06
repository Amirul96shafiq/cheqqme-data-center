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

    // Editing state
    public ?int $editingId = null;

    public string $editingText = '';

    public int $visibleCount = 5; // number of comments to display initially / currently

    public ?int $confirmingDeleteId = null;

    // Track mentions selected from dropdown to avoid relying only on text parsing
    public array $pendingMentionUserIds = [];

    #[On('refreshTaskComments')]
    public function refresh(): void
    {
        // Refresh the component
    }

    // Handle mention selected
    #[On('mentionSelected')]
    public function onMentionSelected(?array $payload = null): void
    {
        \Log::info('🎯 TaskComments::onMentionSelected called', [
            'payload' => $payload,
            'timestamp' => now()->toISOString(),
        ]);

        if (!$payload) {
            \Log::warning('❌ No payload received in onMentionSelected');

            return;
        }

        $userId = $payload['userId'] ?? 0;

        \Log::info('🔍 Processing mentionSelected', [
            'userId' => $userId,
            'userIdType' => gettype($userId),
            'isAll' => $userId === '@all',
            'currentPendingMentions' => $this->pendingMentionUserIds,
        ]);

        // Handle special @all case
        if ($userId === '@all') {
            \Log::info('🎯 Adding @all to pending mentions');
            $this->pendingMentionUserIds[] = '@all';
            \Log::info('✅ @all added to pending mentions', [
                'pendingMentions' => $this->pendingMentionUserIds,
            ]);

            return;
        }

        // Handle regular user IDs
        $userId = (int) $userId;
        if ($userId > 0 && !in_array($userId, $this->pendingMentionUserIds, true)) {
            \Log::info('🎯 Adding regular user to pending mentions', [
                'userId' => $userId,
                'pendingMentions' => $this->pendingMentionUserIds,
            ]);
            $this->pendingMentionUserIds[] = $userId;
        }
    }

    // Mount the component
    public function mount(int $taskId): void
    {
        $this->task = Task::findOrFail($taskId);
        // Ensure base form array keys exist before Filament/Livewire entangle
        $this->composerData = $this->composerData ?? [];
        if (!array_key_exists('newComment', $this->composerData)) {
            $this->composerData['newComment'] = '';
        }
        $this->editData = $this->editData ?? [];
        if (!array_key_exists('editingText', $this->editData)) {
            $this->editData['editingText'] = '';
        }
        if (method_exists($this, 'composerForm')) {
            $this->composerForm->fill(['newComment' => '']);
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
        \Log::info('🔍 Comment mentions extracted', [
            'extractedMentions' => $mentions,
            'pendingMentionUserIds' => $this->pendingMentionUserIds,
            'sanitizedComment' => $sanitized,
        ]);

        // Merge with any user IDs selected via the dropdown tracking
        if (!empty($this->pendingMentionUserIds)) {
            // Check if @all is in pending mentions
            if (in_array('@all', $this->pendingMentionUserIds)) {
                \Log::info('🎯 @all found in pending mentions, overriding all other mentions');
                $mentions = ['@all']; // @all overrides all other mentions
            } else {
                \Log::info('🔄 Merging regular mentions', [
                    'extractedMentions' => $mentions,
                    'pendingMentions' => $this->pendingMentionUserIds,
                ]);
                $mentions = array_values(array_unique(array_merge($mentions, $this->pendingMentionUserIds)));
            }
        }

        \Log::info('✅ Final mentions for comment', [
            'finalMentions' => $mentions,
            'hasAll' => in_array('@all', $mentions),
        ]);

        // Create the comment
        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => auth()->id(),
            'comment' => $sanitized,
            'mentions' => $mentions,
        ]);

        // Process mentions and send notifications
        \Log::info('🚀 Calling processMentions', [
            'commentId' => $comment->id,
            'mentions' => $comment->mentions,
            'hasAll' => in_array('@all', $comment->mentions),
        ]);

        $comment->processMentions();

        \Log::info('✅ processMentions completed', [
            'commentId' => $comment->id,
        ]);

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
        // keep visibleCount stable (newest-first list already includes new comment)
        $this->dispatch('refreshTaskComments');
        // Browser event to forcibly clear editor DOM (fallback)
        $this->dispatch('resetComposerEditor');
    }

    // Start editing a comment
    public function startEdit(int $commentId): void
    {
        $comment = $this->task->comments()->whereNull('deleted_at')->findOrFail($commentId);
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

    // Save editing a comment
    public function saveEdit(): void
    {
        if (!$this->editingId) {
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
            // No change; just exit without notification spam
            $this->cancelEdit();

            return;
        }

        // Extract mentions from updated comment text
        $mentions = Comment::extractMentions($sanitized);
        if (!empty($this->pendingMentionUserIds)) {
            // Check if @all is in pending mentions
            if (in_array('@all', $this->pendingMentionUserIds)) {
                $mentions = ['@all']; // @all overrides all other mentions
            } else {
                $mentions = array_values(array_unique(array_merge($mentions, $this->pendingMentionUserIds)));
            }
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

    // Delete a comment
    public function deleteComment(int $commentId): void
    {
        $comment = $this->task->comments()->findOrFail($commentId);
        if ($comment->user_id !== auth()->id()) {
            return;
        }
        $comment->delete();
        // Send notification
        Notification::make()
            ->title(__('comments.notifications.deleted_title'))
            ->body(Str::limit($comment->comment, 120))
            ->danger()
            ->send();
        // Adjust visibleCount if it exceeds remaining (non-deleted) comments
        $total = $this->task->comments()->whereNull('deleted_at')->count();
        if ($this->visibleCount > $total) {
            $this->visibleCount = $total;
        }
        $this->dispatch('refreshTaskComments');
    }

    // Confirm deleting a comment
    public function confirmDelete(int $commentId): void
    {
        $comment = $this->task->comments()->whereNull('deleted_at')->findOrFail($commentId);
        if ($comment->user_id !== auth()->id()) {
            return;
        }
        $this->confirmingDeleteId = $commentId;
    }

    // Perform deleting a comment
    public function performDelete(): void
    {
        if (!$this->confirmingDeleteId) {
            return;
        }
        $this->deleteComment($this->confirmingDeleteId);
        $this->confirmingDeleteId = null;
    }

    // Cancel deleting a comment
    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    // Get the comments
    public function getCommentsProperty()
    {
        return $this->task->comments()
            ->whereNull('deleted_at')
            ->with('user')
            ->orderByDesc('created_at')
            ->take($this->visibleCount)
            ->get();
    }

    // Get the total comments
    public function getTotalCommentsProperty(): int
    {
        return $this->task->comments()->whereNull('deleted_at')->count();
    }

    // Show more comments
    public function showMore(): void
    {
        $total = $this->task->comments()->whereNull('deleted_at')->count();
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
                    ->extraInputAttributes(['style' => 'min-height:4.5rem;max-height:29rem;overflow-y:auto;resize:vertical;'])
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
                    ->extraInputAttributes(['style' => 'min-height:4.5rem;max-height:15rem;overflow-y:auto;resize:vertical;'])
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
                if ($href && !preg_match('/^https?:\/\//i', $href)) {
                    $href = 'https://' . ltrim($href);
                }
                $safe = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');

                return '<a href="' . $safe . '" target="_blank" rel="nofollow noopener">';
            }, $html);
            // Drop event handlers / javascript: remnants just in case
            $html = preg_replace('/<a([^>]*)(on[a-z]+\s*=\s*"[^"]*")([^>]*)>/i', '<a$1$3>', $html);
            $html = preg_replace('/<a([^>]*)(javascript:)[^>]*>/i', '<a$1>', $html);
        }

        // 6) Strip attributes from all other allowed tags
        $html = preg_replace_callback('/<(?!a\b)(strong|em|s|code|pre|ul|ol|li|br|p)([^>]*)>/i', function ($m) {
            return '<' . strtolower($m[1]) . '>';
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
}
