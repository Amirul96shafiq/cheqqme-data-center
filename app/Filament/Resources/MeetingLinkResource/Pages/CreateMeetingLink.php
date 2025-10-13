<?php

namespace App\Filament\Resources\MeetingLinkResource\Pages;

use App\Filament\Resources\MeetingLinkResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMeetingLink extends CreateRecord
{
    protected static string $resource = MeetingLinkResource::class;

    protected static string $view = 'filament.resources.meeting-link-resource.pages.create-meeting-link';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
