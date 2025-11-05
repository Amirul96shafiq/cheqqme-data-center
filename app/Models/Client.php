<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'staff_information',
        'company_name',
        'company_email',
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
                'staff_information',
                'company_name',
                'company_email',
                'company_address',
                'billing_address',
                'notes',
                'extra_information',
                'created_at',
                'updated_by',
            ])
            ->logOnlyDirty() // Only log when values actually change
            ->useLogName('Clients');
    }

    protected $casts = [
        'extra_information' => 'array',
        'staff_information' => 'array',
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

    public function importantUrls(): HasMany
    {
        return $this->hasMany(ImportantUrl::class);
    }

    public function projectCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->projects->count(),
        );
    }

    public function importantUrlCount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->importantUrls->count(),
        );
    }
}
