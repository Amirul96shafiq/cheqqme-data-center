<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Client extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

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
                'updated_by'
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

}
