<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Filament\Notifications\Actions\Action as FilamentAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class UserMentionedInComment extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
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
        // Keep for audit/API if needed, but primary UI now sends via Filament in Comment::processMentions()
        return ['database'];
    }

    /**
     * Get the notification's database type.
     */
    public function databaseType(object $notifiable): string
    {
        return 'Filament\\Notifications\\DatabaseNotification';
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        $preview = Str::limit(strip_tags($this->comment->comment), 100);

        // Include Filament-compatible keys so it shows in the topbar database notifications
        return [
            // Business payload
            'type' => 'user_mentioned',
            'comment_id' => $this->comment->id,
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'mentioned_by_id' => $this->mentionedBy->id,
            'mentioned_by_username' => $this->mentionedBy->username,
            'comment_preview' => $preview,
            'action_url' => \App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $this->task->id]),

            // Filament notification rendering
            'format' => 'filament',
            'title' => __('You were mentioned in a comment'),
            'body' => (($this->mentionedBy?->username) ?: __('Someone')) . ' ' . __('mentioned you on') . ' "' . (($this->task?->title) ?: __('a task')) . '": ' . $preview,
            'icon' => 'heroicon-o-at-symbol',
            'iconColor' => 'primary',
        ];
    }



    /**
     * Provide Filament-compatible database payload.
     */
    public function toDatabase(object $notifiable): array
    {
        $preview = Str::limit(strip_tags($this->comment->comment), 100);

        return FilamentNotification::make()
            ->title(__('You were mentioned in a comment'))
            ->body((($this->mentionedBy?->username) ?: __('Someone')) . ' ' . __('mentioned you on') . ' "' . (($this->task?->title) ?: __('a task')) . '": ' . $preview)
            ->icon('heroicon-o-at-symbol')
            ->iconColor('primary')
            ->actions([
                FilamentAction::make('view')
                    ->label(__('View task'))
                    ->url(\App\Filament\Resources\TaskResource::getUrl('edit', ['record' => $this->task->id]))
                    ->button(),
            ])
            ->getDatabaseMessage();
    }
}
