<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;

class GetAvatarProvider implements AvatarProvider
{
  public function get(Model $record): string
  {
    if (method_exists($record, 'getFilamentAvatarUrl')) {
      return $record->getFilamentAvatarUrl() ?? $this->getDefaultAvatar($record);
    }

    return $this->getDefaultAvatar($record);
  }

  protected function getDefaultAvatar(Model $record): string
  {
    return 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=FFFFFF&background=111827';
  }
}