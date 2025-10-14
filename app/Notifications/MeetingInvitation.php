<?php

namespace App\Notifications;

use App\Models\MeetingLink;
use Filament\Notifications\Actions\Action as FilamentAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MeetingInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public MeetingLink $meetingLink,
        public string $invitedBy
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
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
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        $meetingTitle = $this->meetingLink->title;
        $startTime = $this->meetingLink->meeting_start_time?->format('j/n/y, h:i A') ?? 'TBD';
        $platform = $this->meetingLink->meeting_platform;

        return FilamentNotification::make()
            ->title('Meeting Invitation')
            ->body("You've been invited to a {$platform} meeting by {$this->invitedBy}")
            ->icon('heroicon-o-video-camera')
            ->iconColor('success')
            ->actions([
                FilamentAction::make('join_meeting')
                    ->label('Join Meeting')
                    ->url($this->meetingLink->meeting_url)
                    ->openUrlInNewTab()
                    ->button()
                    ->color('primary'),
                FilamentAction::make('view_details')
                    ->label('View Details')
                    ->url(\App\Filament\Resources\MeetingLinkResource::getUrl('edit', ['record' => $this->meetingLink->id]))
                    ->link()
                    ->color('gray'),
            ])
            ->getDatabaseMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'meeting_invitation',
            'meeting_id' => $this->meetingLink->id,
            'meeting_title' => $this->meetingLink->title,
            'meeting_platform' => $this->meetingLink->meeting_platform,
            'meeting_start_time' => $this->meetingLink->meeting_start_time?->format('j/n/y, h:i A'),
            'meeting_url' => $this->meetingLink->meeting_url,
            'invited_by' => $this->invitedBy,
        ];
    }
}
