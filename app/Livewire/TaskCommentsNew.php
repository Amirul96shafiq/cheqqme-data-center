<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskCommentsNew extends Component implements HasForms
{
    use AuthorizesRequests;
    use InteractsWithForms;

    public string $newComment = '';

    public ?int $editingId = null;

    public string $editingText = '';

    public int $visibleCount = 10;

    public ?int $confirmingDeleteId = null;

    public bool $showMentionDropdown = false;

    public string $mentionSearch = '';

    public array $filteredUsers = [];

    public int $selectedUserIndex = 0;

    public ?int $taskId = null;

    public ?Task $task = null;

    public function mount(?int $taskId = null): void
    {
        $this->taskId = $taskId;

        if ($this->taskId) {
            $this->task = Task::findOrFail($this->taskId);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('newComment')
                    ->label('Add Comment')
                    ->placeholder('Type your comment here... (Use @username to mention someone)')
                    ->required()
                    ->maxLength(2000)
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'strike',
                        'bulletList',
                        'orderedList',
                        'link',
                        'codeBlock',
                    ])
                    ->extraAttributes(['class' => 'min-h-[80px] max-h-[120px]'])
                    ->extraInputAttributes(['class' => 'min-h-[80px] max-h-[120px]']),
            ]);
    }

    public function addComment(): void
    {
        if (! $this->task) {
            return;
        }

        $this->validate([
            'newComment' => 'required|string|max:2000',
        ]);

        if (trim($this->newComment) === '') {
            return;
        }

        // Extract mentions from comment text
        $mentions = Comment::extractMentions($this->newComment);

        // Create the comment
        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => auth()->id(),
            'comment' => $this->newComment,
            'mentions' => $mentions,
            'mentions_processed' => false,
        ]);

        // Process mentions and send notifications
        $comment->processMentions();

        // Clear the form
        $this->newComment = '';

        // Show success notification
        Notification::make()
            ->title('Comment added successfully')
            ->success()
            ->send();

        // Refresh the component
        $this->dispatch('$refresh');
    }

    public function startEdit(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);

        // Check if user can edit this comment
        if ($comment->user_id !== auth()->id()) {
            Notification::make()
                ->title('Access denied')
                ->body('You can only edit your own comments.')
                ->danger()
                ->send();

            return;
        }

        $this->editingId = $commentId;
        $this->editingText = $comment->comment;
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editingText = '';
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editingText' => 'required|string|max:2000',
        ]);

        if (trim($this->editingText) === '') {
            return;
        }

        $comment = Comment::findOrFail($this->editingId);

        // Check if user can edit this comment
        if ($comment->user_id !== auth()->id()) {
            Notification::make()
                ->title('Access denied')
                ->body('You can only edit your own comments.')
                ->danger()
                ->send();

            return;
        }

        // Extract mentions from edited text
        $mentions = Comment::extractMentions($this->editingText);

        // Update comment
        $comment->update([
            'comment' => $this->editingText,
            'mentions' => $mentions,
            'mentions_processed' => false,
        ]);

        // Process mentions for the edited comment
        $comment->processMentions();

        // Reset edit state
        $this->editingId = null;
        $this->editingText = '';

        // Show success notification
        Notification::make()
            ->title('Comment updated successfully')
            ->success()
            ->send();

        // Refresh the component
        $this->dispatch('$refresh');
    }

    public function confirmDelete(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);

        // Check if user can delete this comment
        if ($comment->user_id !== auth()->id()) {
            Notification::make()
                ->title('Access denied')
                ->body('You can only delete your own comments.')
                ->danger()
                ->send();

            return;
        }

        $this->confirmingDeleteId = $commentId;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function performDelete(): void
    {
        $comment = Comment::findOrFail($this->confirmingDeleteId);

        // Check if user can delete this comment
        if ($comment->user_id !== auth()->id()) {
            Notification::make()
                ->title('Access denied')
                ->body('You can only delete your own comments.')
                ->danger()
                ->send();

            return;
        }

        $comment->delete();

        $this->confirmingDeleteId = null;

        // Show success notification
        Notification::make()
            ->title('Comment deleted successfully')
            ->success()
            ->send();

        // Refresh the component
        $this->dispatch('$refresh');
    }

    public function showMore(): void
    {
        $this->visibleCount += 10;
    }

    public function searchUsers(string $search): void
    {
        if (strlen($search) < 2) {
            $this->filteredUsers = [];
            $this->showMentionDropdown = false;

            return;
        }

        $this->filteredUsers = Comment::searchUsersForMentions($search, 10);
        $this->showMentionDropdown = ! empty($this->filteredUsers);
        $this->selectedUserIndex = 0;
        $this->mentionSearch = $search;
    }

    public function selectUser(int $userId): void
    {
        $user = collect($this->filteredUsers)->firstWhere('id', $userId);
        if ($user) {
            $username = $user['username'] ?: $user['name'];

            // Find the last @ symbol and replace the search term
            $text = $this->newComment;
            $lastAtSymbol = strrpos($text, '@');

            if ($lastAtSymbol !== false) {
                // Find the end of the current search term
                $searchStart = $lastAtSymbol + 1;
                $searchEnd = $searchStart;

                // Find where the search term ends (space, punctuation, or end of string)
                while ($searchEnd < strlen($text) && ! preg_match('/[\s\.,!?\n\r\t]/', $text[$searchEnd])) {
                    $searchEnd++;
                }

                // Replace the search term with the selected username
                $this->newComment = substr($text, 0, $searchStart).$username.' '.substr($text, $searchEnd);
            }
        }

        $this->showMentionDropdown = false;
        $this->filteredUsers = [];
        $this->mentionSearch = '';
    }

    public function getCommentsProperty()
    {
        if (! $this->task) {
            return collect();
        }

        return $this->task->comments()
            ->with(['user', 'task'])
            ->latest()
            ->take($this->visibleCount)
            ->get();
    }

    public function getTotalCommentsProperty()
    {
        if (! $this->task) {
            return 0;
        }

        return $this->task->comments()->count();
    }

    public function getHasMoreCommentsProperty()
    {
        return $this->totalComments > $this->visibleCount;
    }

    #[On('comment-added')]
    public function refreshComments(): void
    {
        $this->dispatch('$refresh');
    }

    public function render()
    {
        if (! $this->task) {
            return view('livewire.task-comments-new', [
                'task' => null,
                'comments' => collect(),
                'totalComments' => 0,
                'hasMoreComments' => false,
            ]);
        }

        return view('livewire.task-comments-new');
    }
}
