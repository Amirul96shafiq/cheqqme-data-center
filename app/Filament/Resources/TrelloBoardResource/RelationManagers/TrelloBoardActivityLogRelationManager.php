<?php

namespace App\Filament\Resources\TrelloBoardResource\RelationManagers;

use Illuminate\Database\Eloquent\Model;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class TrelloBoardActivityLogRelationManager extends ActivitylogRelationManager
{
 public static function getTitle(Model $ownerRecord, string $pageClass): string
 {
  return __('trelloboard.section.activity_logs');
 }

 public static function getBadge(Model $ownerRecord, string $pageClass): ?string
 {
  // Use the morphMany relationship to count activities
  $count = $ownerRecord->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')->count();

  return $count > 0 ? (string) $count : null;
 }
}
