<?php

namespace App\Filament\Pages\Base;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Schema;

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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userId = auth()->id();

        if (! $userId) {
            return parent::mutateFormDataBeforeCreate($data);
        }

        $model = static::getResource()::getModel();
        $modelInstance = app($model);
        $table = $modelInstance->getTable();

        if (Schema::hasColumn($table, 'updated_by') && ! array_key_exists('updated_by', $data)) {
            $data['updated_by'] = $userId;
        }

        if (Schema::hasColumn($table, 'created_by') && ! array_key_exists('created_by', $data)) {
            $data['created_by'] = $userId;
        }

        return parent::mutateFormDataBeforeCreate($data);
    }
}
