<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Event extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'start_datetime',
        'end_datetime',
        'location_title',
        'location_full_address',
        'location_place_id',
        'meeting_link_id',
        'featured_image',
        'invited_user_ids',
        'project_ids',
        'document_ids',
        'important_url_ids',
        'extra_information',
        'google_calendar_event_id',
        'synced_to_calendar',
        'visibility_status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'invited_user_ids' => 'array',
        'project_ids' => 'array',
        'document_ids' => 'array',
        'important_url_ids' => 'array',
        'extra_information' => 'array',
        'synced_to_calendar' => 'boolean',
    ];

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'description',
                'event_type',
                'start_datetime',
                'end_datetime',
                'location_title',
                'location_full_address',
                'meeting_link_id',
                'featured_image',
                'invited_user_ids',
                'project_ids',
                'document_ids',
                'important_url_ids',
                'synced_to_calendar',
                'visibility_status',
                'updated_at',
                'updated_by',
            ])
            ->logOnlyDirty()
            ->useLogName('Events');
    }

    public function meetingLink(): BelongsTo
    {
        return $this->belongsTo(MeetingLink::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get invited users
     */
    public function invitedUsers()
    {
        return User::whereIn('id', $this->invited_user_ids ?? [])->get();
    }

    /**
     * Get related projects
     */
    public function projects()
    {
        return Project::whereIn('id', $this->project_ids ?? [])->get();
    }

    /**
     * Get related documents
     */
    public function documents()
    {
        return Document::whereIn('id', $this->document_ids ?? [])->get();
    }

    /**
     * Get related important URLs
     */
    public function importantUrls()
    {
        return ImportantUrl::whereIn('id', $this->important_url_ids ?? [])->get();
    }

    /**
     * Get the featured image URL accessor
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image ? asset('storage/'.$this->featured_image) : null;
    }

    /**
     * Scope to get visible events for the current user.
     * Active events are visible to all users.
     * Draft events are only visible to their creator.
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
     * Check if the event is visible to a specific user.
     */
    public function isVisibleToUser($userId = null): bool
    {
        $userId = $userId ?? auth()->id();

        return $this->visibility_status === 'active' || $this->created_by === $userId;
    }

    /**
     * Get the Google Maps URL for the event location.
     */
    public function getGoogleMapsUrl(): ?string
    {
        // Always prioritize place URL format when we have a location title
        if ($this->location_title) {
            // URL encode the place name (replace spaces with +, encode special chars)
            $encodedPlaceName = str_replace(' ', '+', $this->location_title);
            $encodedPlaceName = urlencode($encodedPlaceName);

            // If we have a place_id, include it in the data parameter for precise location
            if ($this->location_place_id) {
                return "https://www.google.com/maps/place/{$encodedPlaceName}/data=!4m2!3m1!1s{$this->location_place_id}";
            }

            // Use place URL format even without place_id - Google Maps will search for the place name
            return "https://www.google.com/maps/place/{$encodedPlaceName}";
        }

        // Fallback to search URL if we have an address but no title
        if ($this->location_full_address) {
            $encodedQuery = urlencode($this->location_full_address);

            return "https://www.google.com/maps/search/?api=1&query={$encodedQuery}";
        }

        return null;
    }
}
