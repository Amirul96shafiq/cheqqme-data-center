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
        // Use plain text for parsing
        $text = trim(strip_tags($commentText));

        if ($text === '') {
            return [];
        }

        // Find raw candidates: @ followed by up to 5 space-separated tokens
        preg_match_all('/@([A-Za-z0-9_\.\-]+(?:\s+[A-Za-z0-9_\.\-]+){0,4})/u', $text, $allMatches, PREG_OFFSET_CAPTURE);

        // If no matches, return empty array
        if (empty($allMatches[1])) {
            return [];
        }

        // Build candidate variants (longest-first) to resolve against DB
        $candidateSet = [];
        $occurrences = [];
        foreach ($allMatches[1] as $match) {
            [$value, $offset] = $match; // value without leading '@'
            $parts = preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY);
            if (!$parts) {
                continue;
            }
            $variants = [];
            for ($n = count($parts); $n >= 1; $n--) {
                $variant = implode(' ', array_slice($parts, 0, $n));
                $variants[] = $variant;
                $candidateSet[$variant] = true;
            }
            $occurrences[] = [
                'offset' => $offset,
                'variants' => $variants, // longest first
            ];
        }

        // Get candidates
        $candidates = array_keys($candidateSet);
        // If no candidates, return empty array
        if (empty($candidates)) {
            return [];
        }

        // Query users by exact username or exact display name
        $users = User::query()
            ->whereIn('username', $candidates)
            ->orWhereIn('name', $candidates)
            ->get(['id', 'username', 'name']);

        if ($users->isEmpty()) {
            return [];
        }

        // Build lookup maps for fast resolution
        $byUsername = [];
        $byName = [];
        foreach ($users as $u) {
            if (!empty($u->username)) {
                $byUsername[$u->username] = (int) $u->id;
            }
            if (!empty($u->name)) {
                $byName[$u->name] = (int) $u->id;
            }
        }

        // Get found IDs
        $foundIds = [];
        foreach ($occurrences as $occ) {
            foreach ($occ['variants'] as $variant) {
                // longest-first
                if (isset($byUsername[$variant])) {
                    $foundIds[] = $byUsername[$variant];
                    break;
                }
                if (isset($byName[$variant])) {
                    $foundIds[] = $byName[$variant];
                    break;
                }
            }
        }

        if (empty($foundIds)) {
            return [];
        }

        return array_values(array_unique($foundIds));
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['task_id', 'user_id', 'comment', 'mentions'])
            ->logOnlyDirty() // Only log when values actually change
            ->useLogName('Comments');
    }

    // Get the rendered comment
    public function getRenderedCommentAttribute(): string
    {
        $html = $this->comment ?? '';

        // Resolve mentioned users from stored IDs
        $mentionIds = is_array($this->mentions) ? $this->mentions : [];
        $mentionUsers = empty($mentionIds)
            ? collect()
            : User::query()->whereIn('id', $mentionIds)->get(['username', 'name']);

        // Build alternatives to match exactly (prefer longer names first)
        $terms = [];
        foreach ($mentionUsers as $u) {
            if (!empty($u->name)) {
                $terms[] = '@' . $u->name;
            }
            if (!empty($u->username)) {
                $terms[] = '@' . $u->username;
            }
        }
        // Deduplicate and sort by length desc to avoid partial overlaps
        $terms = array_values(array_unique(array_filter($terms, static fn($t) => $t !== '')));
        usort($terms, static fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        // If no known terms, fall back to original content
        if (empty($terms)) {
            return $html;
        }

        // Escape terms for regex
        $escapedTerms = array_map(static fn($t) => preg_quote($t, '/'), $terms);
        $alternation = implode('|', $escapedTerms);

        $parts = preg_split('/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $inside = ['code' => 0, 'pre' => 0, 'a' => 0];
        $out = '';

        // Process parts
        foreach ($parts as $part) {
            if ($part !== '' && $part[0] === '<') {
                if (preg_match('/^<\s*\/\s*(\w+)/', $part, $m)) {
                    $tag = strtolower($m[1]);
                    if (array_key_exists($tag, $inside) && $inside[$tag] > 0) {
                        $inside[$tag]--;
                    }
                } elseif (preg_match('/^<\s*(\w+)/', $part, $m)) {
                    $tag = strtolower($m[1]);
                    if (array_key_exists($tag, $inside)) {
                        $inside[$tag]++;
                    }
                }
                $out .= $part;
            } else {
                if ($inside['code'] || $inside['pre'] || $inside['a']) {
                    $out .= $part;
                } else {
                    // Only wrap exact known terms with trailing boundary
                    $out .= preg_replace_callback('/(' . $alternation . ')(?=[\s\.,;:!\?\)]|$|<)/u', function ($m) {
                        $text = $m[1]; // e.g., @Full Name or @username

                        return '<span class="mention">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</span>';
                    }, $part);
                }
            }
        }

        return $out;
    }
}
