<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Client extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'pic_name',
        'pic_email',
        'pic_contact_number',
        'company_name',
        'company_address',
        'billing_address',
        'notes',
        'updated_by',
        'extra_information',
    ];

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'pic_name',
                'pic_email',
                'pic_contact_number',
                'company_name',
                'company_address',
                'billing_address',
                'notes',
                'extra_information',
                'created_at',
                'updated_by',
            ])
            ->useLogName('Clients');
    }

    protected $casts = [
        'extra_information' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($client) {
            if (empty($client->company_name)) {
                $client->company_name = $client->pic_name;
            }
        });

    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function documents(): HasManyThrough
    {
        return $this->hasManyThrough(Document::class, Project::class);
    }
}
