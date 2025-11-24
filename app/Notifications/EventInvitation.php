<?php

namespace App\Notifications;

use App\Models\Event;
use Filament\Notifications\Actions\Action as FilamentAction;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EventInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event,
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
        $eventTitle = $this->event->title;
        $startTime = $this->event->start_datetime?->format('j/n/y, h:i A') ?? 'TBD';
        $eventType = $this->event->event_type;

        return FilamentNotification::make()
            ->title(__('event.notifications.invitation_title'))
            ->body(__('event.notifications.invitation_body', [
                'eventType' => $eventType,
                'invitedBy' => $this->invitedBy,
                'eventTitle' => $eventTitle,
            ]))
            ->icon('heroicon-o-calendar')
            ->iconColor('success')
            ->actions([
                FilamentAction::make('view_event')
                    ->label(__('event.notifications.view_event'))
                    ->url(\App\Filament\Resources\EventResource::getUrl('edit', ['record' => $this->event->id]))
                    ->link()
                    ->color('primary'),
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
            'type' => 'event_invitation',
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_type' => $this->event->event_type,
            'event_start_time' => $this->event->start_datetime?->format('j/n/y, h:i A'),
            'event_end_time' => $this->event->end_datetime?->format('j/n/y, h:i A'),
            'meeting_url' => $this->event->meeting_link?->meeting_url,
            'invited_by' => $this->invitedBy,
        ];
    }
}
