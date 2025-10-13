<?php

namespace App\Filament\Resources\MeetingLinkResource\Pages;

use App\Filament\Resources\MeetingLinkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeetingLink extends EditRecord
{
    protected static string $resource = MeetingLinkResource::class;

    protected static string $view = 'filament.resources.meeting-link-resource.pages.edit-meeting-link';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
