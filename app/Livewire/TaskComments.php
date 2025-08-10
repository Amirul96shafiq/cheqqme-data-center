<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// (Filament Actions removed for nested component stability)

class TaskComments extends Component
{
  use AuthorizesRequests;

  public Task $task;
  public string $newComment = '';
  // Editing state
  public ?int $editingId = null;
  public string $editingText = '';
  public int $visibleCount = 5; // number of comments to display initially / currently
  public ?int $confirmingDeleteId = null;

  protected $rules = [
    'newComment' => 'required|string|max:1000',
    'editingText' => 'required|string|max:1000',
  ];

  protected $listeners = [
    'refreshTaskComments' => '$refresh',
  ];

  public function mount(int $taskId): void
  {
    $this->task = Task::findOrFail($taskId);
  }

  public function addComment(): void
  {
    $this->validateOnly('newComment');
    $trimmed = trim($this->newComment);
    if ($trimmed === '') {
      return;
    }
    Comment::create([
      'task_id' => $this->task->id,
      'user_id' => auth()->id(),
      'comment' => $trimmed,
    ]);
    $this->newComment = '';
    // Increase visible window so the previously oldest visible comment does not disappear
    $this->visibleCount++;
    $this->dispatch('refreshTaskComments');
  }

  public function startEdit(int $commentId): void
  {
    $comment = $this->task->comments()->whereNull('deleted_at')->findOrFail($commentId);
    if ($comment->user_id !== auth()->id())
      return;
    $this->editingId = $comment->id;
    $this->editingText = $comment->comment;
  }

  public function cancelEdit(): void
  {
    $this->editingId = null;
    $this->editingText = '';
  }

  public function saveEdit(): void
  {
    if (!$this->editingId)
      return;
    $this->validateOnly('editingText');
    $comment = $this->task->comments()->findOrFail($this->editingId);
    if ($comment->user_id !== auth()->id())
      return;
    $comment->update(['comment' => trim($this->editingText)]);
    $this->cancelEdit();
    $this->dispatch('refreshTaskComments');
  }

  public function deleteComment(int $commentId): void
  {
    $comment = $this->task->comments()->findOrFail($commentId);
    if ($comment->user_id !== auth()->id()) {
      return;
    }
    $comment->delete();
    // Adjust visibleCount if it exceeds remaining (non-deleted) comments
    $total = $this->task->comments()->whereNull('deleted_at')->count();
    if ($this->visibleCount > $total) {
      $this->visibleCount = $total;
    }
    $this->dispatch('refreshTaskComments');
  }

  public function confirmDelete(int $commentId): void
  {
    $comment = $this->task->comments()->findOrFail($commentId);
    if ($comment->user_id !== auth()->id())
      return;
    $this->confirmingDeleteId = $commentId;
  }

  public function performDelete(): void
  {
    if (!$this->confirmingDeleteId)
      return;
    $this->deleteComment($this->confirmingDeleteId);
    $this->confirmingDeleteId = null;
  }

  public function cancelDelete(): void
  {
    $this->confirmingDeleteId = null;
  }

  public function getCommentsProperty()
  {
    return $this->task->comments()
      ->whereNull('deleted_at')
      ->with('user')
      ->orderByDesc('created_at')
      ->take($this->visibleCount)
      ->get();
  }

  public function getTotalCommentsProperty(): int
  {
    return $this->task->comments()->whereNull('deleted_at')->count();
  }

  public function showMore(): void
  {
    $total = $this->task->comments()->whereNull('deleted_at')->count();
    $remaining = $total - $this->visibleCount;
    if ($remaining <= 0)
      return;
    $this->visibleCount += min(5, $remaining);
    $this->dispatch('comments-show-more');
  }

  public function render()
  {
    return view('livewire.task-comments');
  }

  // Filament Actions removed; keeping component lean
}
