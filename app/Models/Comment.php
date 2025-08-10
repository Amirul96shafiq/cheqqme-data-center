<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Comment extends Model
{
  use SoftDeletes, LogsActivity;

  protected $fillable = [
    'task_id',
    'user_id',
    'comment',
  ];

  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  public function task(): BelongsTo
  {
    return $this->belongsTo(Task::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function getActivityLogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['task_id', 'user_id', 'comment'])
      ->useLogName('Comments');
  }
}
