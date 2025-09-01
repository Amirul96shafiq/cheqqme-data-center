<?php

namespace App\Filament\Resources\TrelloBoardResource\Pages;

use App\Filament\Resources\TrelloBoardResource;
use Filament\Actions;
use App\Filament\Pages\Base\BaseEditRecord;

class EditTrelloBoard extends BaseEditRecord
{
    protected static string $resource = TrelloBoardResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    public function getContentTabLabel(): ?string
    {
        return __('trelloboard.labels.edit-trello-board');
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
