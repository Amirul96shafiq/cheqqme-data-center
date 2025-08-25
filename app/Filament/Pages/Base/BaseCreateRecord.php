<?php

namespace App\Filament\Pages\Base;

use Filament\Resources\Pages\CreateRecord;

abstract class BaseCreateRecord extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    public static function getCreateButtonLabel(): string
    {
        return static::$createButtonLabel ?? __('filament::resources/pages/create-record.form.actions.create.label');
    }
}
