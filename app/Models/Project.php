<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;
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
        'created_by',
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
                'created_by',
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

    public function trackingTokens(): Relation
    {
        // Return a HasMany relationship as a placeholder for Filament RelationManager
        // The actual query is overridden in TrackingTokensRelationManager::getTableQuery()
        // This is needed because Filament RelationManagers require a relationship property
        return $this->hasMany(Task::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
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

    public function getTrackingTokensCountAttribute()
    {
        $projectId = $this->id;

        return Task::whereNotNull('tracking_token')
            ->where(function (Builder $query) use ($projectId) {
                $query
                    ->whereJsonContains('project', $projectId)
                    ->orWhereJsonContains('project', (string) $projectId)
                    ->orWhere('project', 'like', '%"'.$projectId.'"%')
                    ->orWhere('project', 'like', '%['.$projectId.']%');
            })
            ->count();
    }

    public function getTrackingTokens()
    {
        $projectId = $this->id;

        return Task::whereNotNull('tracking_token')
            ->where(function (Builder $query) use ($projectId) {
                $query
                    ->whereJsonContains('project', $projectId)
                    ->orWhereJsonContains('project', (string) $projectId)
                    ->orWhere('project', 'like', '%"'.$projectId.'"%')
                    ->orWhere('project', 'like', '%['.$projectId.']%');
            })
            ->orderBy('created_at', 'desc')
            ->select(['tracking_token', 'title', 'status', 'created_at'])
            ->get()
            ->map(function ($task) {
                return [
                    'token' => $task->tracking_token,
                    'title' => $task->title,
                    'status' => $task->status,
                    'created_at' => $task->created_at->format('m/d/Y, g:i A'),
                    'url' => route('issue-tracker.status', ['token' => $task->tracking_token]),
                ];
            });
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
