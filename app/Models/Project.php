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
        'visibility_status',
        'notes',
        'created_by',
        'updated_by',
        'extra_information',
        'issue_tracker_code',
        'wishlist_tracker_code',
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
                'visibility_status',
                'notes',
                'extra_information',
                'created_by',
                'updated_at',
                'updated_by',
                'issue_tracker_code',
                'wishlist_tracker_code',
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
        // Prefer eager-loaded count when available to avoid N+1 queries.
        if (array_key_exists('documents_count', $this->attributes)) {
            return (int) $this->attributes['documents_count'];
        }

        return $this->documents()->count();
    }

    public function getImportantUrlCountAttribute()
    {
        // Prefer eager-loaded count when available to avoid N+1 queries.
        if (array_key_exists('important_urls_count', $this->attributes)) {
            return (int) $this->attributes['important_urls_count'];
        }

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

    public function getWishlistTokensCountAttribute()
    {
        $projectId = $this->id;

        return Task::wishlistTokens()
            ->where(function (Builder $query) use ($projectId) {
                $query
                    ->whereJsonContains('project', $projectId)
                    ->orWhereJsonContains('project', (string) $projectId)
                    ->orWhere('project', 'like', '%"'.$projectId.'"%')
                    ->orWhere('project', 'like', '%['.$projectId.']%');
            })
            ->count();
    }

    public function getWishlistTokens()
    {
        $projectId = $this->id;

        return Task::wishlistTokens()
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
                    'url' => route('wishlist-tracker.status', ['token' => $task->tracking_token]),
                ];
            });
    }

    /**
     * Scope to get visible projects for the current user.
     * Active projects are visible to all users.
     * Draft projects are only visible to their creator.
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
     * Check if the project is visible to a specific user.
     */
    public function isVisibleToUser($userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return $this->visibility_status === 'active' || $this->created_by === $userId;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            if (empty($project->issue_tracker_code)) {
                $project->issue_tracker_code = static::generateIssueTrackerCode();
            }
            if (empty($project->wishlist_tracker_code)) {
                $project->wishlist_tracker_code = static::generateWishlistTrackerCode();
            }
        });

        static::updating(function ($project) {
            if (empty($project->issue_tracker_code)) {
                $project->issue_tracker_code = static::generateIssueTrackerCode();
            }
            if (empty($project->wishlist_tracker_code)) {
                $project->wishlist_tracker_code = static::generateWishlistTrackerCode();
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

    /**
     * Generate a unique 6-character wishlist tracker code.
     * Format: 3 uppercase letters + 3 digits, randomly arranged.
     */
    public static function generateWishlistTrackerCode(): string
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
        } while (static::where('wishlist_tracker_code', $code)->exists());

        return $code;
    }
}
