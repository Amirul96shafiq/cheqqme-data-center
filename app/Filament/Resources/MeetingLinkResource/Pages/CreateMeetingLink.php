<?php

namespace App\Filament\Resources\MeetingLinkResource\Pages;

use App\Filament\Resources\MeetingLinkResource;
use App\Notifications\MeetingInvitation;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMeetingLink extends CreateRecord
{
    protected static string $resource = MeetingLinkResource::class;

    protected static string $view = 'filament.resources.meeting-link-resource.pages.create-meeting-link';

    public function mount(): void
    {
        parent::mount();

        // Check for Google Calendar connection flash messages
        if (session('success')) {
            Notification::make()
                ->title(__('meetinglink.notifications.google_calendar_connected'))
                ->body(__('meetinglink.notifications.google_calendar_connected_body'))
                ->success()
                ->send();

            session()->forget('success');
        }

        if (session('error')) {
            Notification::make()
                ->title(__('meetinglink.notifications.google_calendar_connection_failed'))
                ->body(session('error'))
                ->danger()
                ->send();

            session()->forget('error');
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send notifications to invited users
        $userIds = $this->record->user_ids ?? [];

        if (! empty($userIds)) {
            $invitedBy = auth()->user()->name ?? auth()->user()->username ?? 'Unknown';
            $notifiedCount = 0;

            foreach ($userIds as $userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $user->notify(new MeetingInvitation($this->record, $invitedBy));
                    $notifiedCount++;
                }
            }

            // Show success notification
            if ($notifiedCount > 0) {
                $attendeeWord = $notifiedCount === 1
                    ? __('meetinglink.table.attendee')
                    : __('meetinglink.table.attendees_plural');

                Notification::make()
                    ->title(__('meetinglink.notifications.invitations_sent_title'))
                    ->body(__('meetinglink.notifications.invitations_sent_body', [
                        'count' => $notifiedCount,
                        'attendee' => $attendeeWord,
                    ]))
                    ->success()
                    ->send();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
