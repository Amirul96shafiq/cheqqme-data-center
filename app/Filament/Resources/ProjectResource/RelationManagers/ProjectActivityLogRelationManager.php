<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Illuminate\Database\Eloquent\Model;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;

class ProjectActivityLogRelationManager extends ActivitylogRelationManager
{
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('project.section.activity_logs');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        // Use the morphMany relationship to count activities
        $count = $ownerRecord->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject')->count();

        return $count > 0 ? (string) $count : null;
    }
}
