<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Kenepa\ResourceLock\Models\Concerns\HasLocks;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
    use HasFactory, HasLocks, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'client',
        'project',
        'document',
        'important_url',
        'assigned_to',
        'status',
        'order_column',
        'due_date',
        'updated_by',
        'extra_information',
        'attachments',
    ];

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title',
                'description',
                'client',
                'project',
                'document',
                'assigned_to',
                'status',
                'order_column',
                'due_date',
                'created_at',
                'extra_information',
                'updated_at',
                'updated_by',
                'attachments',
            ])
            ->useLogName('Tasks');
    }

    protected $casts = [
        'client' => 'integer',
        'project' => 'array',
        'document' => 'array',
        'important_url' => 'array',
        'extra_information' => 'array',
        'attachments' => 'array',
    ];

    /**
     * Mutator to ensure assigned_to is always an array when setting.
     */
    public function setAssignedToAttribute($value)
    {
        if (is_null($value)) {
            $this->attributes['assigned_to'] = json_encode([]);

            return;
        }

        if (is_array($value)) {
            $this->attributes['assigned_to'] = json_encode($value);

            return;
        }

        // Handle single integer values
        if (is_numeric($value)) {
            $this->attributes['assigned_to'] = json_encode([$value]);

            return;
        }

        $this->attributes['assigned_to'] = json_encode([]);
    }

    /**
     * Accessor to ensure assigned_to is always an array when getting.
     */
    public function getAssignedToAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }

        // If it's already an array (from cast), return it
        if (is_array($value)) {
            return $value;
        }

        // Handle legacy integer values
        if (is_numeric($value)) {
            return [$value];
        }

        // Try to decode JSON
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return [];
    }

    // Make virtual attributes available when converting to array / JSON for the Kanban adapter
    protected $appends = [
        'assigned_to_badge',
        'assigned_to_extra_count',
        'assigned_to_extra_count_self',
        'due_date_red',
        'due_date_yellow',
        'due_date_green',
        'due_date_gray',
        'featured_image',
        'message_count',
        'attachment_count',
        'resource_count',
    ];

    /**
     * Returns only one assigned_to badge per card: highlighted for self, otherwise normal.
     */
    public function getAssignedToBadgeAttribute(): ?string
    {
        $firstUser = $this->first_assigned_user;
        if (! $firstUser) {
            return null;
        }

        $authId = Auth::id();
        if ($authId && $firstUser->id === $authId) {
            return $firstUser->short_name ?? $firstUser->username ?? $firstUser->name ?? null;
        }

        return $firstUser->short_name ?? $firstUser->username ?? $firstUser->name ?? null;
    }

    /**
     * Returns the total count of assigned users.
     */
    public function getAssignedToCountAttribute(): int
    {
        $assignedTo = $this->assigned_to;
        if (! $assignedTo || ! is_array($assignedTo)) {
            return 0;
        }

        return count($assignedTo);
    }

    /**
     * Returns the count of assigned users excluding the current user.
     */
    public function getAssignedToOthersCountAttribute(): int
    {
        $assignedTo = $this->assigned_to;
        if (! $assignedTo || ! is_array($assignedTo)) {
            return 0;
        }

        $authId = Auth::id();
        if (! $authId) {
            return count($assignedTo);
        }

        // Count users excluding current user
        return count(array_filter($assignedTo, function ($userId) use ($authId) {
            return $userId != $authId;
        }));
    }

    /**
     * Returns the count of additional assigned users (total - 1).
     * This is used for the "+X" badge display.
     */
    public function getAssignedToExtraCountAttribute(): ?string
    {
        $assignedTo = $this->assigned_to;
        if (! $assignedTo || ! is_array($assignedTo)) {
            return null;
        }

        $totalCount = count($assignedTo);

        if ($totalCount <= 1) {
            return null; // No extra users to show
        }

        $extraCount = $totalCount - 1;

        return "+{$extraCount}";
    }

    /**
     * Returns the extra assigned users count for current user (highlighted).
     * This is used for the "+X" badge display when current user is among extra users.
     */
    public function getAssignedToExtraCountSelfAttribute(): ?string
    {
        $assignedTo = $this->assigned_to;
        if (! $assignedTo || ! is_array($assignedTo)) {
            return null;
        }

        $totalCount = count($assignedTo);

        if ($totalCount <= 1) {
            return null; // No extra users to show
        }

        $authId = Auth::id();
        if (! $authId) {
            return null; // No current user
        }

        // Get all assigned users
        $users = $this->assignedToUsers();
        if ($users->isEmpty()) {
            return null;
        }

        // Get the first user (main assigned user)
        $firstUser = $users->first();

        // Check if current user is among the extra users (excluding the first user)
        $extraUsers = $users->filter(function ($user) use ($firstUser) {
            return $user->id !== $firstUser->id;
        });

        // If current user is in the extra users list, show the count
        if ($extraUsers->contains('id', $authId)) {
            $extraCount = $totalCount - 1;

            return "+{$extraCount}";
        }

        return null;
    }

    /**
     * Returns only one assigned_to badge per card: highlighted for self, otherwise normal.
     */
    public function getAssignedToDisplayAttribute(): ?string
    {
        $firstUser = $this->first_assigned_user;
        if (! $firstUser) {
            return null;
        }

        return $firstUser->short_name ?? $firstUser->username ?? $firstUser->name ?? null;
    }

    public function getAssignedToDisplayColorAttribute(): string
    {
        $users = $this->assignedToUsers();
        if (empty($users)) {
            return 'gray';
        }

        $authId = Auth::id();
        if (! $authId) {
            return 'gray';
        }

        // Check if current user is in the assigned users list
        $currentUserAssigned = $users->contains('id', $authId);

        if ($currentUserAssigned) {
            return 'cyan'; // Highlight for self
        }

        return 'gray'; // Normal for others
    }

    public function getAssignedToDisplayIconAttribute(): string
    {
        $users = $this->assignedToUsers();
        if (empty($users)) {
            return 'heroicon-o-user';
        }

        $authId = Auth::id();
        if (! $authId) {
            return 'heroicon-o-user';
        }

        // Check if current user is in the assigned users list
        $currentUserAssigned = $users->contains('id', $authId);

        if ($currentUserAssigned) {
            return 'heroicon-m-user'; // Icon for self
        }

        return 'heroicon-o-user'; // Icon for others
    }

    /**
     * Returns only one assigned_to badge per card: highlighted for self, otherwise normal.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get all assigned users for this task.
     */
    public function assignedToUsers()
    {
        $assignedTo = $this->assigned_to;
        if (! $assignedTo || ! is_array($assignedTo)) {
            return collect();
        }

        // Get users in the same order as they appear in the assigned_to array
        $users = User::whereIn('id', $assignedTo)->get()->keyBy('id');
        $orderedUsers = collect();

        foreach ($assignedTo as $userId) {
            if ($users->has($userId)) {
                $orderedUsers->push($users->get($userId));
            }
        }

        return $orderedUsers;
    }

    /**
     * Get the first assigned user from the array.
     */
    public function getFirstAssignedUserAttribute()
    {
        $users = $this->assignedToUsers();

        return $users->first();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->with('user');
    }

    /**
     * Expose the related user's username for Kanban card display.
     */
    public function getAssignedToUsernameAttribute(): ?string
    {
        $firstUser = $this->first_assigned_user;
        if (! $firstUser) {
            return null;
        }

        // Always show username, even for current user
        return $firstUser->short_name ?? $firstUser->username ?? $firstUser->name ?? null;
    }

    public function getAssignedToUsernameSelfAttribute(): ?string
    {
        $firstUser = $this->first_assigned_user;
        if (! $firstUser) {
            return null;
        }
        $authId = Auth::id();
        if ($authId && $firstUser->id === $authId) {
            return $firstUser->short_name ?? $firstUser->username ?? $firstUser->name ?? null;
        }

        return null;
    }

    /**
     * Helper to compute days difference for due date color logic.
     */
    protected function dueDateDiffInDays(): ?int
    {
        if (! $this->due_date) {
            return null;
        }
        try {
            $due = \Carbon\Carbon::parse($this->due_date);

            return now()->diffInDays($due, false); // negative if past
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Only one of these urgency accessors will return a formatted date; others return null so that
     * we can map static colors per virtual attribute via Flowforge config (no dynamic per-record colors supported).
     */

    // Red due date - less than 1 day away
    public function getDueDateRedAttribute(): ?string
    {
        $diff = $this->dueDateDiffInDays();
        if ($diff !== null && $diff < 1) {
            return $this->formattedDueDate();
        }

        return null;
    }

    // Yellow due date - 1 to 6 days away
    public function getDueDateYellowAttribute(): ?string
    {
        $diff = $this->dueDateDiffInDays();
        if ($diff !== null && $diff >= 1 && $diff < 7) {
            return $this->formattedDueDate();
        }

        return null;
    }

    // Green due date - 14 days or more away
    public function getDueDateGreenAttribute(): ?string
    {
        $diff = $this->dueDateDiffInDays();
        if ($diff !== null && $diff >= 14) {
            return $this->formattedDueDate();
        }

        return null;
    }

    // Gray due date - 7 to 13 days away
    public function getDueDateGrayAttribute(): ?string
    {
        $diff = $this->dueDateDiffInDays();
        if ($diff !== null && $diff >= 7 && $diff < 14) {
            return $this->formattedDueDate();
        }

        return null;
    }

    protected function formattedDueDate(): string
    {
        try {
            return \Carbon\Carbon::parse($this->due_date)->format('j/n/y');
        } catch (\Throwable $e) {
            return (string) $this->due_date;
        }
    }

    /**
     * Returns the first image from attachments as the featured image for kanban cards.
     */
    public function getFeaturedImageAttribute(): ?string
    {
        if (! $this->attachments || ! is_array($this->attachments)) {
            return null;
        }

        // Image file extensions to look for
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

        foreach ($this->attachments as $attachment) {
            if (! is_string($attachment)) {
                continue;
            }

            $extension = strtolower(pathinfo($attachment, PATHINFO_EXTENSION));

            if (in_array($extension, $imageExtensions)) {
                // Return the full URL to the attachment
                return asset('storage/'.$attachment);
            }
        }

        return null;
    }

    /**
     * Returns the total count of comments/messages for this task.
     */
    public function getMessageCountAttribute(): ?int
    {
        return $this->comments()->count();
    }

    /**
     * Returns the total count of attachments for this task.
     */
    public function getAttachmentCountAttribute(): ?int
    {
        return $this->attachments ? count($this->attachments) : 0;
    }

    /**
     * Returns the total count of selected resources (projects, documents, important URLs) for this task.
     */
    public function getResourceCountAttribute(): ?int
    {
        $count = 0;

        // Count client
        if ($this->client) {
            $count += 1;
        }

        // Count projects
        if ($this->project && is_array($this->project)) {
            $count += count($this->project);
        }

        // Count documents
        if ($this->document && is_array($this->document)) {
            $count += count($this->document);
        }

        // Count important URLs
        if ($this->important_url && is_array($this->important_url)) {
            $count += count($this->important_url);
        }

        return $count;
    }

    public function shouldLogEvent(string $eventName): bool
    {
        // Prevent trait from logging "updated" when moving columns/status
        if (
            $eventName === 'updated'
            && ($this->isDirty('order_column') || $this->isDirty('status'))
        ) {
            return false;
        }

        return true;
    }
}
