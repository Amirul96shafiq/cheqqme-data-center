<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class UserMentionedInComment extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Comment $comment,
        public Task $task,
        public User $mentionedBy
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_mentioned',
            'comment_id' => $this->comment->id,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'mentioned_by_id' => $this->mentionedBy->id,
            'mentioned_by_username' => $this->mentionedBy->username,
            'comment_preview' => \Illuminate\Support\Str::limit(strip_tags($this->comment->comment), 100),
            'action_url' => \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $this->task->id]),
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'user_mentioned';
    }
}
