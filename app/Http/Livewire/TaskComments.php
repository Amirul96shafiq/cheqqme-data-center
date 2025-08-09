<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskComments extends Component
{
  use AuthorizesRequests;

  public Task $task;
  public string $newComment = '';
  public ?int $editingId = null;
  public string $editingText = '';

  protected $rules = [
    'newComment' => 'required|string|max:1000',
    'editingText' => 'required|string|max:1000',
  ];

  protected $listeners = [
    'refreshTaskComments' => '$refresh',
  ];

  public function mount(int $taskId)
  {
    $this->task = Task::findOrFail($taskId);
  }

  public function addComment()
  {
    $this->validateOnly('newComment');
    if (trim($this->newComment) === '') {
      return;
    }
    Comment::create([
      'task_id' => $this->task->id,
      'user_id' => auth()->id(),
      'comment' => $this->newComment,
    ]);
    $this->newComment = '';
    $this->emitSelf('refreshTaskComments');
  }

  public function startEdit(int $commentId)
  {
    $comment = $this->task->comments()->whereNull('deleted_at')->findOrFail($commentId);
    if ($comment->user_id !== auth()->id()) {
      return; // silently ignore
    }
    $this->editingId = $comment->id;
    $this->editingText = $comment->comment;
  }

  public function cancelEdit()
  {
    $this->editingId = null;
    $this->editingText = '';
  }

  public function saveEdit()
  {
    if (!$this->editingId)
      return;
    $this->validateOnly('editingText');
    $comment = $this->task->comments()->findOrFail($this->editingId);
    if ($comment->user_id !== auth()->id()) {
      return;
    }
    $comment->update(['comment' => $this->editingText]);
    $this->cancelEdit();
    $this->emitSelf('refreshTaskComments');
  }

  public function deleteComment(int $commentId)
  {
    $comment = $this->task->comments()->findOrFail($commentId);
    if ($comment->user_id !== auth()->id()) {
      return;
    }
    $comment->delete();
    // Stay in same state; refresh list
    $this->emitSelf('refreshTaskComments');
  }

  public function getCommentsProperty()
  {
    return $this->task->comments()
      ->whereNull('deleted_at')
      ->with('user')
      ->orderByDesc('created_at')
      ->get();
  }

  public function render()
  {
    return view('livewire.task-comments');
  }
}
