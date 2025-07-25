<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Pages\Base\BaseCreateRecord;

class CreateProject extends BaseCreateRecord
{
    protected static string $resource = ProjectResource::class;
}
