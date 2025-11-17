<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Document extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'type',
        'url',
        'file_path',
        'project_id',
        'notes',
        'visibility_status',
        'created_by',
        'updated_by',
        'extra_information',
    ];

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'type',
                'url',
                'file_path',
                'project_id',
                'notes',
                'visibility_status',
                'extra_information',
                'updated_at',
                'updated_by',
            ])
            ->logOnlyDirty() // Only log when values actually change
            ->useLogName('Documents');
    }

    protected $casts = [
        'extra_information' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get visible documents for the current user.
     * Active documents are visible to all users.
     * Draft documents are only visible to their creator.
     */
    public function scopeVisibleToUser($query, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        return $query->where(function ($q) use ($userId) {
            $q->where('visibility_status', 'active')
                ->orWhere(function ($q2) use ($userId) {
                    $q2->where('visibility_status', 'draft')
                        ->where('created_by', $userId);
                });
        });
    }

    /**
     * Check if the document is visible to a specific user.
     */
    public function isVisibleToUser($userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return $this->visibility_status === 'active' || $this->created_by === $userId;
    }
}
