<?php

namespace App\Filament\Resources\MeetingLinkResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\MeetingLinkResource;
use Filament\Actions;

class EditMeetingLink extends BaseEditRecord
{
    protected static string $resource = MeetingLinkResource::class;

    protected static string $view = 'filament.resources.meeting-link-resource.pages.edit-meeting-link';

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
