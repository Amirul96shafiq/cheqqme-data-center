<?php

namespace App\Filament\Pages\Base;

use Filament\Resources\Pages\EditRecord;

abstract class BaseEditRecord extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /**
     * Enable unsaved changes alert for this page
     */
    protected function hasUnsavedDataChangesAlert(): bool
    {
        return true;
    }
}
