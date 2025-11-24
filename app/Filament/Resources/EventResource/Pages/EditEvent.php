<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Notifications\EventInvitation;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected static string $view = 'filament.resources.event-resource.pages.edit-event';

    protected ?array $originalUserIds = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Capture the original invited_user_ids when the form loads
        $this->originalUserIds = $data['invited_user_ids'] ?? [];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = Auth::id();

        return $data;
    }

    protected function afterSave(): void
    {
        // Get the new invited_user_ids after save
        $newUserIds = $this->record->invited_user_ids ?? [];

        // Find newly added users by comparing with the original captured during form load
        $newlyAddedUserIds = array_diff($newUserIds, $this->originalUserIds ?? []);

        if (! empty($newlyAddedUserIds)) {
            $invitedBy = auth()->user()->name ?? auth()->user()->username ?? 'Unknown';
            $notifiedCount = 0;

            foreach ($newlyAddedUserIds as $userId) {
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
                    ->title(__('event.notifications.new_invitations_sent_title'))
                    ->body(__('event.notifications.new_invitations_sent_body', [
                        'count' => $notifiedCount,
                        'attendee' => $attendeeWord,
                    ]))
                    ->success()
                    ->send();
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Enable unsaved changes alert for this page
     */
    protected function hasUnsavedDataChangesAlert(): bool
    {
        return true;
    }
}
