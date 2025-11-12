<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kenepa\ResourceLock\Models\Concerns\HasLocks;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TrelloBoard extends Model
{
    use HasFactory, HasLocks, LogsActivity, SoftDeletes;

    protected $table = 'trello_boards';

    protected $fillable = [
        'name',
        'url',
        'notes',
        'show_on_boards',
        'created_by',
        'updated_by',
        'extra_information',
    ];

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'url',
                'notes',
                'show_on_boards',
                'extra_information',
                'updated_at',
                'updated_by',
            ])
            ->logOnlyDirty() // Only log when values actually change
            ->useLogName('Trello Boards');
    }

    protected $casts = [
        'extra_information' => 'array',
        'show_on_boards' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($trelloBoard) {
            if (empty($trelloBoard->created_by)) {
                if (auth()->check()) {
                    $trelloBoard->created_by = auth()->id();
                } else {
                    // Fallback: use the first admin user if no authenticated user
                    $adminUser = User::where('is_admin', true)->first();
                    if ($adminUser) {
                        $trelloBoard->created_by = $adminUser->id;
                    } else {
                        // Last resort: use the first user
                        $firstUser = User::first();
                        if ($firstUser) {
                            $trelloBoard->created_by = $firstUser->id;
                        }
                    }
                }
            }
        });
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
