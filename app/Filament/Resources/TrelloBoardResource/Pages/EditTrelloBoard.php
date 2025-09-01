<?php

namespace App\Filament\Resources\TrelloBoardResource\Pages;

use App\Filament\Pages\Base\BaseEditRecord;
use App\Filament\Resources\TrelloBoardResource;
use Filament\Actions;

class EditTrelloBoard extends BaseEditRecord
{
    protected static string $resource = TrelloBoardResource::class;

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
            Actions\DeleteAction::make()
                ->label(__('trelloboard.actions.delete')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label(__('trelloboard.actions.delete')),
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
