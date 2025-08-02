<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use Filament\Actions;
use App\Filament\Pages\Base\BaseCreateRecord;

class CreateClient extends BaseCreateRecord
{
    protected static string $resource = ClientResource::class;

    public function getTitle(): string
    {
        return __('client.actions.create');
    }
}
