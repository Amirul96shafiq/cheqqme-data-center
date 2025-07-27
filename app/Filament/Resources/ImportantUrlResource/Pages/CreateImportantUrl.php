<?php

namespace App\Filament\Resources\ImportantUrlResource\Pages;

use App\Filament\Resources\ImportantUrlResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Pages\Base\BaseCreateRecord;

class CreateImportantUrl extends BaseCreateRecord
{
    protected static string $resource = ImportantUrlResource::class;
}
