<?php

namespace App\Models;

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
      // Use UserMentionedInComment notification class
      $user->notify(new \App\Notifications\UserMentionedInComment($this, $this->task, $this->user));
    }
  }

  /**
   * Extract user mentions from comment text
   */
  public static function extractMentions(string $commentText): array
  {
    // First try with the original HTML content (some editors add spans around mentions)
    $originalText = $commentText;

    // Then also try with plain text to avoid HTML tags interfering with parsing
    $plainText = trim(strip_tags($commentText));

    // Extract all possible username formats
    $patterns = [
      // Username with spaces (e.g., @Amirul Other Account)
      '/(?:^|[\s>])@([A-Za-z0-9_\.\-]+(?: +[A-Za-z0-9_\.\-]+)+)(?=[\s\.,;:!\?\)]|$|\s|<)/u',

      // Regular username without spaces (e.g., @author1)
      '/(?:^|[\s>])@([A-Za-z0-9_\.\-]+)(?=[\s\.,;:!\?\)]|$|\s|<)/u',
    ];

    $usernames = [];

    // Apply each pattern to both original and plain text
    foreach ([$originalText, $plainText] as $text) {
      foreach ($patterns as $pattern) {
        preg_match_all($pattern, $text, $matches);
        if (!empty($matches[1])) {
          $usernames = array_merge($usernames, $matches[1]);
        }
      }
    }

    // Filter and deduplicate
    $usernames = array_values(array_unique(array_filter($usernames, static fn($u) => $u !== '')));

    if (empty($usernames)) {
      return [];
    }

    // Look up users by username
    return User::query()
      ->whereIn('username', $usernames)
      ->pluck('id')
      ->unique()
      ->values()
      ->toArray();
  }

  public function getActivityLogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->logOnly(['task_id', 'user_id', 'comment', 'mentions'])
      ->useLogName('Comments');
  }
}
