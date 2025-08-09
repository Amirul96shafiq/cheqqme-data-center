<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Task extends Model
{
  use HasFactory, SoftDeletes, LogsActivity;

  protected $fillable = [
    'title',
    'description',
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
    'extra_information' => 'array',
  ];

  // Make virtual attributes available when converting to array / JSON for the Kanban adapter
  protected $appends = [
    'assigned_to_username',
    'due_date_red',
    'due_date_yellow',
    'due_date_green',
    'due_date_gray',
  ];

  public function updatedBy()
  {
    return $this->belongsTo(User::class, 'updated_by');
  }

  public function assignedTo()
  {
    return $this->belongsTo(User::class, 'assigned_to');
  }

  /**
   * Expose the related user's username for Kanban card display.
   */
  public function getAssignedToUsernameAttribute(): ?string
  {
    return $this->assignedTo?->username;
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
  public function getDueDateRedAttribute(): ?string
  {
    $diff = $this->dueDateDiffInDays();
    if ($diff !== null && $diff < 1) {
      return $this->formattedDueDate();
    }
    return null;
  }

  public function getDueDateYellowAttribute(): ?string
  {
    $diff = $this->dueDateDiffInDays();
    if ($diff !== null && $diff >= 1 && $diff < 7) {
      return $this->formattedDueDate();
    }
    return null;
  }

  public function getDueDateGreenAttribute(): ?string
  {
    $diff = $this->dueDateDiffInDays();
    if ($diff !== null && $diff >= 14) {
      return $this->formattedDueDate();
    }
    return null;
  }

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
      return \Carbon\Carbon::parse($this->due_date)->format('Y-m-d');
    } catch (\Throwable $e) {
      return (string) $this->due_date;
    }
  }
}