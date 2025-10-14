<?php

namespace App\Filament\Resources\MeetingLinkResource\Pages;

use App\Filament\Resources\MeetingLinkResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListMeetingLinks extends ListRecords
{
    protected static string $resource = MeetingLinkResource::class;

    public function mount(): void
    {
        parent::mount();

        // Check for flash message from Google Calendar connection
        if (session('success')) {
            Notification::make()
                ->title(__('meetinglink.notifications.google_calendar_connected'))
                ->body(__('meetinglink.notifications.google_calendar_connected_body'))
                ->success()
                ->send();

            session()->forget('success');
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('meetinglink.actions.create')),
        ];
    }
}

