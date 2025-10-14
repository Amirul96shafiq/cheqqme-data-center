<?php

namespace App\Filament\Resources\MeetingLinkResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\MeetingLinkResource;
use App\Notifications\MeetingInvitation;
use Filament\Actions;
use Filament\Notifications\Notification;

class EditMeetingLink extends BaseEditRecord
{
    protected static string $resource = MeetingLinkResource::class;

    protected static string $view = 'filament.resources.meeting-link-resource.pages.edit-meeting-link';

    protected ?array $originalUserIds = null;

    public function mount(int | string $record): void
    {
        parent::mount($record);

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

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Capture the original user_ids when the form loads
        $this->originalUserIds = $data['user_ids'] ?? [];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function afterSave(): void
    {
        // Get the new user_ids after save
        $newUserIds = $this->record->user_ids ?? [];

        // Find newly added users by comparing with the original captured during form load
        $newlyAddedUserIds = array_diff($newUserIds, $this->originalUserIds ?? []);

        if (! empty($newlyAddedUserIds)) {
            $invitedBy = auth()->user()->name ?? auth()->user()->username ?? 'Unknown';
            $notifiedCount = 0;

            foreach ($newlyAddedUserIds as $userId) {
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
                    ->title(__('meetinglink.notifications.new_invitations_sent_title'))
                    ->body(__('meetinglink.notifications.new_invitations_sent_body', [
                        'count' => $notifiedCount,
                        'attendee' => $attendeeWord,
                    ]))
                    ->success()
                    ->send();
            }
        }
    }
}
