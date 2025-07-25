<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Pages\Base\BaseCreateRecord;

class CreateDocument extends BaseCreateRecord
{
    protected static string $resource = DocumentResource::class;
}
