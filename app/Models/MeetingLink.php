<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MeetingLink extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'client_ids',
        'project_ids',
        'document_ids',
        'important_url_ids',
        'user_ids',
        'meeting_platform',
        'meeting_url',
        'meeting_id',
        'meeting_start_time',
        'meeting_duration',
        'notes',
        'extra_information',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'client_ids' => 'array',
        'project_ids' => 'array',
        'document_ids' => 'array',
        'important_url_ids' => 'array',
        'user_ids' => 'array',
        'extra_information' => 'array',
        'meeting_start_time' => 'datetime',
    ];

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'client_ids',
                'project_id',
                'document_ids',
                'meeting_platform',
                'meeting_url',
                'notes',
                'extra_information',
                'updated_at',
                'updated_by',
            ])
            ->logOnlyDirty()
            ->useLogName('Meeting Links');
    }

    public function projects()
    {
        return Project::whereIn('id', $this->project_ids ?? [])->get();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function clients()
    {
        return Client::whereIn('id', $this->client_ids ?? [])->get();
    }

    public function documents()
    {
        return Document::whereIn('id', $this->document_ids ?? [])->get();
    }

    public function importantUrls()
    {
        return \App\Models\ImportantUrl::whereIn('id', $this->important_url_ids ?? [])->get();
    }

    public function users()
    {
        return User::whereIn('id', $this->user_ids ?? [])->get();
    }
}
