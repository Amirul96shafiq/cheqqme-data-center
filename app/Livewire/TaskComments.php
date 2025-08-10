<?php

namespace App\Livewire;

use Livewire\Component;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Form;
use App\Models\Task;
use App\Models\Comment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
// (Filament Actions removed for nested component stability)

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

  public function addComment(): void
  {
    // Pull latest value from composer form state
    if (method_exists($this, 'composerForm')) {
      $state = $this->composerForm->getState();
      $this->newComment = $this->normalizeEditorInput($state['newComment'] ?? $this->newComment);
    }
    $this->validateOnly('newComment');
    $sanitized = $this->sanitizeHtml($this->newComment);
    $textOnly = trim(strip_tags($sanitized));
    if ($textOnly === '') {
      return;
    }
    $comment = Comment::create([
      'task_id' => $this->task->id,
      'user_id' => auth()->id(),
      'comment' => $sanitized,
    ]);
    Notification::make()
      ->title('Comment added')
      ->body(Str::limit($textOnly, 120))
      ->success()
      ->send();
    $this->newComment = '';
    if (method_exists($this, 'composerForm')) {
      $this->composerForm->fill(['newComment' => '']);
    }
    $this->composerData['newComment'] = '';
    // keep visibleCount stable (newest-first list already includes new comment)
    $this->dispatch('refreshTaskComments');
    // Browser event to forcibly clear editor DOM (fallback)
    $this->dispatch('resetComposerEditor');
  }

  public function startEdit(int $commentId): void
  {
    $comment = $this->task->comments()->whereNull('deleted_at')->findOrFail($commentId);
    if ($comment->user_id !== auth()->id())
      return;
    $this->editingId = $comment->id;
    $this->editingText = $comment->comment;
    // Ensure underlying form state array has key
    $this->editData = $this->editData ?? [];
    $this->editData['editingText'] = $this->editingText;
    if (method_exists($this, 'editForm')) {
      $this->editForm->fill(['editingText' => $this->editingText]);
    }
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
    if (method_exists($this, 'editForm')) {
      $state = $this->editForm->getState();
      $this->editingText = $this->normalizeEditorInput($state['editingText'] ?? $this->editingText);
    }
    $this->validateOnly('editingText');
    $comment = $this->task->comments()->findOrFail($this->editingId);
    if ($comment->user_id !== auth()->id())
      return;
    $original = $comment->comment;
    $sanitized = $this->sanitizeHtml($this->editingText);
    $plain = trim(strip_tags($sanitized));
    if ($plain === '') {
      Notification::make()->title('Comment not updated')->body('Edited comment cannot be empty.')->danger()->send();
      return;
    }
    if ($sanitized === $original) {
      // No change; just exit without notification spam
      $this->cancelEdit();
      return;
    }
    $comment->update(['comment' => $sanitized]);
    Notification::make()
      ->title('Comment updated')
      ->body(Str::limit($plain, 120))
      ->success()
      ->send();
    $this->cancelEdit();
    $this->dispatch('refreshTaskComments');
  }

  public function updatedComposerData($value, $key): void
  {
    if ($key === 'newComment') {
      $this->newComment = $this->normalizeEditorInput($value);
      if (method_exists($this, 'composerForm')) {
        $this->composerForm->fill(['newComment' => $this->newComment]);
      }
    }
  }

  public function updatedEditData($value, $key): void
  {
    if ($key === 'editingText') {
      $this->editingText = $this->normalizeEditorInput($value);
      if (method_exists($this, 'editForm')) {
        $this->editForm->fill(['editingText' => $this->editingText]);
      }
    }
  }

  public function deleteComment(int $commentId): void
  {
    $comment = $this->task->comments()->findOrFail($commentId);
    if ($comment->user_id !== auth()->id()) {
      return;
    }
    $comment->delete();
    Notification::make()
      ->title('Comment deleted')
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
    // Final pre-render safeguard: clear any accidental 'undefined' before view output
    if (method_exists($this, 'composerForm')) {
      $state = $this->composerForm->getState();
      if (isset($state['newComment']) && is_string($state['newComment']) && \Illuminate\Support\Str::lower(trim(strip_tags($state['newComment']))) === 'undefined') {
        $this->composerForm->fill(['newComment' => '']);
        $this->newComment = '';
      }
    }
    return view('livewire.task-comments');
  }

  public function hydrate(): void
  {
    // Defensive: clear any literal 'undefined' string that may slip into state before rendering
    if (is_string($this->newComment) && \Illuminate\Support\Str::lower(trim(strip_tags($this->newComment))) === 'undefined') {
      $this->newComment = '';
    }
    if (method_exists($this, 'composerForm')) {
      $state = $this->composerForm->getState();
      if (isset($state['newComment']) && is_string($state['newComment']) && \Illuminate\Support\Str::lower(trim(strip_tags($state['newComment']))) === 'undefined') {
        $this->composerForm->fill(['newComment' => '']);
      }
      // Keep backing array in sync for entangle path reliability
      $this->composerData['newComment'] = $state['newComment'] ?? '';
    }
  }

  protected function getForms(): array
  {
    return [
      'composerForm' => $this->makeForm()->schema([
        RichEditor::make('newComment')
          ->label('')
          ->placeholder('Start typing your comment here')
          ->toolbarButtons(['bold', 'italic', 'strike', 'bulletList', 'orderedList', 'link', 'codeBlock'])
          ->extraAttributes(['class' => 'minimal-comment-editor'])
          ->maxLength(1000)
          ->extraInputAttributes(['style' => 'min-height:4.5rem;max-height:4.5rem;overflow-y:auto;'])
          ->default('')
          ->formatStateUsing(function ($state) {
            if (is_string($state) && \Illuminate\Support\Str::lower(trim(strip_tags($state))) === 'undefined') {
              return '';
            }
            return $state;
          })
          ->mutateDehydratedStateUsing(function (?string $state) {
            return (is_string($state) && \Illuminate\Support\Str::lower(trim(strip_tags($state))) === 'undefined') ? '' : $state;
          })
          ->columnSpanFull(),
      ])->statePath('composerData'),
      'editForm' => $this->makeForm()->schema([
        RichEditor::make('editingText')
          ->label('')
          ->placeholder('Edit comment...')
          ->toolbarButtons(['bold', 'italic', 'strike', 'bulletList', 'orderedList', 'link', 'codeBlock'])
          ->maxLength(1000)
          ->default('')
          ->formatStateUsing(function ($state) {
            if (is_string($state) && \Illuminate\Support\Str::lower(trim(strip_tags($state))) === 'undefined') {
              return '';
            }
            return $state;
          })
          ->columnSpanFull(),
      ])->statePath('editData'),
    ];
  }

  // Filament Actions removed; keeping component lean

  private function sanitizeHtml(?string $html): string
  {
    $html = $html ?? '';
    // Normalize line breaks from contenteditable (convert div/p breaks to <br>) for simple paragraphs
    // Remove script/style tags completely
    $html = preg_replace('/<(script|style)[^>]*?>.*?<\/\1>/is', '', $html);
    // Allow only a whitelist of tags
    $allowed = '<b><strong><i><em><s><del><strike><code><pre><ul><ol><li><a><br><p>'; // blockquote removed
    $html = strip_tags($html, $allowed);
    // Remove on* attributes & javascript: href
    // Process anchors
    if (stripos($html, '<a') !== false) {
      $html = preg_replace_callback('/<a\s+([^>]+)>/i', function ($m) {
        $attr = $m[1];
        // Extract href
        if (preg_match('/href\s*=\s*"([^"]*)"/i', $attr, $hrefMatch)) {
          $href = $hrefMatch[1];
        } elseif (preg_match("/href\s*=\s*'([^']*)'/i", $attr, $hrefMatch)) {
          $href = $hrefMatch[1];
        } else {
          $href = '';
        }
        if ($href && !preg_match('/^https?:\/\//i', $href)) {
          $href = 'https://' . ltrim($href); // force https
        }
        $safe = htmlspecialchars($href, ENT_QUOTES, 'UTF-8');
        return '<a href="' . $safe . '" target="_blank" rel="nofollow noopener">';
      }, $html);
    }
    // Normalize <strike> to <s>
    $html = preg_replace_callback('/<\/?strike>/i', function ($m) {
      return str_starts_with($m[0], '</') ? '</s>' : '<s>';
    }, $html);
    // Strip any remaining attributes except for <a href target rel> (blockquote removed)
    $html = preg_replace_callback('/<(?!a\b)(b|strong|i|em|s|del|code|pre|ul|ol|li|br)([^>]*)>/i', function ($m) {
      return '<' . strtolower($m[1]) . '>';
    }, $html);
    // Remove existing blockquote tags, keeping inner content
    if (stripos($html, '<blockquote') !== false) {
      $html = preg_replace('/<blockquote[^>]*>/i', '', $html);
      $html = preg_replace('/<\/blockquote>/i', '', $html);
    }
    // Remove event handlers from anchors
    $html = preg_replace('/<a([^>]*)(on[a-z]+\s*=\s*"[^"]*")([^>]*)>/i', '<a$1$3>', $html);
    $html = preg_replace('/<a([^>]*)(javascript:)[^>]*>/i', '<a$1>', $html);
    // Collapse excessive <br>
    $html = preg_replace('/(<br\s*\/?>\s*){3,}/i', '<br><br>', $html);
    // Trim whitespace
    $html = trim($html);
    return $html;
  }

  private function normalizeEditorInput(?string $value): string
  {
    $value = $value ?? '';
    $plain = trim(strip_tags($value));
    $lower = Str::lower($plain);
    if ($lower === 'undefined' || $lower === 'null' || $lower === '"undefined"') {
      return '';
    }
    return $value;
  }
}
