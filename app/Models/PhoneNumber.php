<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PhoneNumber extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'phone',
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
                'phone',
                'notes',
                'visibility_status',
                'extra_information',
                'updated_at',
                'updated_by',
            ])
            ->logOnlyDirty() // Only log when values actually change
            ->useLogName('Phone Numbers');
    }

    protected $casts = [
        'extra_information' => 'array',
    ];

    protected $dates = ['deleted_at'];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get visible phone numbers for the current user.
     * Active phone numbers are visible to all users.
     * Draft phone numbers are only visible to their creator.
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
     * Check if the phone number is visible to a specific user.
     */
    public function isVisibleToUser($userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return $this->visibility_status === 'active' || $this->created_by === $userId;
    }
}
