<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Project extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'project_url',
        'client_id',
        'description',
        'status',
        'notes',
        'updated_by',
        'extra_information',
        'issue_tracker_code',
    ];

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'project_url',
                'client_id',
                'description',
                'status',
                'notes',
                'extra_information',
                'updated_at',
                'updated_by',
            ])
            ->logOnlyDirty() // Only log when values actually change
            ->useLogName('Projects');
    }

    protected $casts = [
        'extra_information' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function importantUrls(): HasMany
    {
        return $this->hasMany(ImportantUrl::class);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getDocumentCountAttribute()
    {
        return $this->documents()->count();
    }

    public function getImportantUrlCountAttribute()
    {
        return $this->importantUrls()->count();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->issue_tracker_code)) {
                $project->issue_tracker_code = static::generateIssueTrackerCode();
            }
        });

        static::updating(function ($project) {
            if (empty($project->issue_tracker_code)) {
                $project->issue_tracker_code = static::generateIssueTrackerCode();
            }
        });
    }

    /**
     * Generate a unique 6-character issue tracker code.
     * Format: 3 uppercase letters + 3 digits, randomly arranged.
     */
    public static function generateIssueTrackerCode(): string
    {
        do {
            // Generate 3 random uppercase letters
            $letters = '';
            for ($i = 0; $i < 3; $i++) {
                $letters .= chr(65 + random_int(0, 25)); // A-Z
            }

            // Generate 3 random digits
            $digits = '';
            for ($i = 0; $i < 3; $i++) {
                $digits .= random_int(0, 9);
            }

            // Combine and shuffle
            $characters = str_split($letters.$digits);
            shuffle($characters);
            $code = implode('', $characters);

            // Check uniqueness
        } while (static::where('issue_tracker_code', $code)->exists());

        return $code;
    }
}
