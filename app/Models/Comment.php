<?php

namespace App\Models;

use App\Notifications\UserMentionedInComment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Comment extends Model
{
  use LogsActivity, SoftDeletes;

  protected $fillable = [
    'task_id',
    'user_id',
    'comment',
    'mentions',
  ];

  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'mentions' => 'array',
  ];

  public function task(): BelongsTo
  {
    return $this->belongsTo(Task::class);
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get mentioned users
   */
  public function getMentionedUsersAttribute()
  {
    if (!$this->mentions || !is_array($this->mentions)) {
      return collect();
    }

    return User::whereIn('id', $this->mentions)->get();
  }

  /**
   * Process mentions in comment text and send notifications
   */
  public function processMentions()
  {
    if (!$this->mentions || !is_array($this->mentions)) {
      return;
    }

    $mentionedUsers = User::whereIn('id', $this->mentions)->get();

    foreach ($mentionedUsers as $user) {
      // Don't notify the comment author
      if ($user->id === $this->user_id) {
        continue;
      }

      $user->notify(new UserMentionedInComment($this, $this->task, $this->user));
    }
  }

  /**
   * Extract user mentions from comment text
   */
  public static function extractMentions(string $commentText): array
  {
    preg_match_all('/@(\w+)/', $commentText, $matches);

    if (empty($matches[1])) {
      return [];
    }

    $usernames = $matches[1];
    $userIds = User::whereIn('username', $usernames)
      ->pluck('id')
      ->toArray();

    return $userIds;
  }

  public function getActivityLogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['task_id', 'user_id', 'comment', 'mentions'])
      ->useLogName('Comments');
  }
}
