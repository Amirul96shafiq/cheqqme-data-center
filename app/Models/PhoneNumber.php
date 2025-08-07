<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PhoneNumber extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'title',
        'phone',
        'notes',
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
            ->useLogName('Phone Numbers');
    }

    protected $casts = [
        'extra_information' => 'array',
    ];
    protected $dates = ['deleted_at'];

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
