<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Task extends Model
{
  use HasFactory, LogsActivity, SoftDeletes;

  protected $fillable = [
    'title',
    'description',
    'client',
    'project',
    'document',
    'assigned_to',
    'status',
    'order_column',
    'due_date',
    'updated_by',
    'extra_information',
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
      ])
      ->useLogName('Tasks');
  }

  protected $casts = [
    'client' => 'integer',
    'project' => 'array',
    'document' => 'array',
    'extra_information' => 'array',
  ];

  // Make virtual attributes available when converting to array / JSON for the Kanban adapter
  protected $appends = [
    'assigned_to_badge',
    'due_date_red',
    'due_date_yellow',
    'due_date_green',
    'due_date_gray',
  ];

  /**
   * Returns only one assigned_to badge per card: highlighted for self, otherwise normal.
   */
  public function getAssignedToBadgeAttribute(): ?string
  {
    $user = $this->assignedTo;
    if (!$user) {
      return null;
    }
    $authId = Auth::id();
    if ($authId && $user->id === $authId) {
      return $user->short_name ?? $user->username ?? $user->name ?? null;
    }

    return $user->short_name ?? $user->username ?? $user->name ?? null;
  }

  /**
   * Returns only one assigned_to badge per card: highlighted for self, otherwise normal.
   */
  public function getAssignedToDisplayAttribute(): ?string
  {
    $user = $this->assignedTo;
    if (!$user) {
      return null;
    }

    return $user->short_name ?? $user->username ?? $user->name ?? null;
  }

  public function getAssignedToDisplayColorAttribute(): string
  {
    $user = $this->assignedTo;
    $authId = Auth::id();
    if ($user && $authId && $user->id === $authId) {
      return 'cyan'; // Highlight for self
    }
    if ($user) {
      return 'gray'; // Normal for others
    }

    return 'gray'; // Default
  }

  public function getAssignedToDisplayIconAttribute(): string
  {
    $user = $this->assignedTo;
    $authId = Auth::id();
    if ($user && $authId && $user->id === $authId) {
      return 'heroicon-m-user'; // Icon for self
    }
    if ($user) {
      return 'heroicon-o-user'; // Icon for others
    }

    return 'heroicon-o-user'; // Default
  }

  /**
   * Returns only one assigned_to badge per card: highlighted for self, otherwise normal.
   */
  public function updatedBy()
  {
    return $this->belongsTo(User::class, 'updated_by');
  }

  public function assignedTo()
  {
    return $this->belongsTo(User::class, 'assigned_to');
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
    $user = $this->assignedTo;
    if (!$user) {
      return null;
    }

    // Always show username, even for current user
    return $user->short_name ?? $user->username ?? $user->name ?? null;
  }

  public function getAssignedToUsernameSelfAttribute(): ?string
  {
    $user = $this->assignedTo;
    if (!$user) {
      return null;
    }
    $authId = Auth::id();
    if ($authId && $user->id === $authId) {
      return $user->short_name ?? $user->username ?? $user->name ?? null;
    }

    return null;
  }

  /**
   * Helper to compute days difference for due date color logic.
   */
  protected function dueDateDiffInDays(): ?int
  {
    if (!$this->due_date) {
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
