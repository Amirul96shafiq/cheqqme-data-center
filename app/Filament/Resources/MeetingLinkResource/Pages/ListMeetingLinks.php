<?php

namespace App\Filament\Resources\MeetingLinkResource\Pages;

use App\Filament\Resources\MeetingLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMeetingLinks extends ListRecords
{
    protected static string $resource = MeetingLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(__('meetinglink.actions.create')),
        ];
    }
}

