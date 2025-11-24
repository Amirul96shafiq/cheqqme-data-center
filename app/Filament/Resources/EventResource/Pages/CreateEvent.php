<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Notifications\EventInvitation;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        // Send notifications to invited users
        $userIds = $this->record->invited_user_ids ?? [];

        if (! empty($userIds)) {
            $invitedBy = auth()->user()->name ?? auth()->user()->username ?? 'Unknown';
            $notifiedCount = 0;

            foreach ($userIds as $userId) {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $user->notify(new EventInvitation($this->record, $invitedBy));
                    $notifiedCount++;
                }
            }

            // Show success notification
            if ($notifiedCount > 0) {
                $attendeeWord = $notifiedCount === 1
                    ? __('event.table.attendee')
                    : __('event.table.attendees_plural');

                Notification::make()
                    ->title(__('event.notifications.invitations_sent_title'))
                    ->body(__('event.notifications.invitations_sent_body', [
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
