<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use App\Filament\Pages\Base\BaseCreateRecord;

class CreateUser extends BaseCreateRecord
{
    protected static string $resource = UserResource::class;
}
