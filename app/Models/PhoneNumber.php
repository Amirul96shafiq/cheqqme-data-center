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
}
