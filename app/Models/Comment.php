<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Comment extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'task_id',
        'user_id',
        'parent_id',
        'comment',
        'mentions',
        'status',
        'deleted_at',
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

    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    /**
     * Get the parent comment (for replies)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Get the replies to this comment
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }

    /**
     * Get the emoji reactions for the comment.
     */
    public function emojiReactions(): HasMany
    {
        return $this->hasMany(CommentEmojiReaction::class);
    }

    /**
     * Get the current user's emoji reaction for this comment.
     */
    public function currentUserEmojiReaction(): HasOne
    {
        return $this->hasOne(CommentEmojiReaction::class)->where('user_id', auth()->id());
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
        \Log::info('🚀 Comment::processMentions called', [
            'commentId' => $this->id,
            'mentions' => $this->mentions,
            'mentionsType' => gettype($this->mentions),
            'isArray' => is_array($this->mentions),
            'hasMentions' => !empty($this->mentions),
        ]);

        if (!$this->mentions || !is_array($this->mentions)) {
            \Log::warning('❌ No mentions to process', [
                'mentions' => $this->mentions,
                'isArray' => is_array($this->mentions),
            ]);

            return;
        }

        // Check if @Everyone is mentioned
        if (in_array('@Everyone', $this->mentions)) {
            \Log::info('🎯 @Everyone mention detected, processing all users');

            // Get all users including the comment author (consistent with regular mentions)
            $allUsers = User::all();

            \Log::info('👥 Users to notify', [
                'userCount' => $allUsers->count(),
                'users' => $allUsers->pluck('username')->toArray(),
                'commentAuthorId' => $this->user_id,
            ]);

            foreach ($allUsers as $user) {
                \Log::info('📧 Sending notification to user', [
                    'userId' => $user->id,
                    'username' => $user->username,
                ]);

                try {
                    // Use UserMentionedInComment notification class
                    $user->notify(new \App\Notifications\UserMentionedInComment($this, $this->task, $this->user));
                    \Log::info('✅ Notification sent successfully', [
                        'userId' => $user->id,
                        'username' => $user->username,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('❌ Failed to send notification', [
                        'userId' => $user->id,
                        'username' => $user->username,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            \Log::info('✅ @Everyone notifications processing completed');

            return; // Exit early since @Everyone covers everyone
        }

        \Log::info('👤 Processing regular user mentions', [
            'mentionIds' => $this->mentions,
        ]);

        // Regular user mentions
        $mentionedUsers = User::whereIn('id', $this->mentions)->get();

        foreach ($mentionedUsers as $user) {
            \Log::info('📧 Sending notification to mentioned user', [
                'userId' => $user->id,
                'username' => $user->username,
            ]);

            try {
                // Use UserMentionedInComment notification class
                $user->notify(new \App\Notifications\UserMentionedInComment($this, $this->task, $this->user));
                \Log::info('✅ Regular notification sent successfully', [
                    'userId' => $user->id,
                    'username' => $user->username,
                ]);
            } catch (\Exception $e) {
                \Log::error('❌ Failed to send regular notification', [
                    'userId' => $user->id,
                    'username' => $user->username,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        \Log::info('✅ Regular notifications processing completed');
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

        $mentions = [];

        // Check for @Everyone mention
        if (preg_match('/@everyone\b/i', $text)) {
            $mentions[] = '@Everyone';
        }

        // Find raw candidates: @ followed by up to 5 space-separated tokens
        preg_match_all('/@([A-Za-z0-9_\.\-]+(?:\s+[A-Za-z0-9_\.\-]+){0,4})/u', $text, $allMatches, PREG_OFFSET_CAPTURE);

        // If no individual mentions found, return what we have (might be just @Everyone)
        if (empty($allMatches[1])) {
            return $mentions;
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
        // If no candidates, return what we have
        if (empty($candidates)) {
            return $mentions;
        }

        // Query users by exact username or exact display name
        $users = User::query()
            ->whereIn('username', $candidates)
            ->orWhereIn('name', $candidates)
            ->get(['id', 'username', 'name']);

        if ($users->isEmpty()) {
            return $mentions;
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

        // Merge individual user mentions with @Everyone (if present)
        $mentions = array_merge($mentions, array_values(array_unique($foundIds)));

        return array_values(array_unique($mentions));
    }

    public function getActivityLogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['task_id', 'user_id', 'comment', 'mentions'])
            ->logOnlyDirty() // Only log when values actually change
            ->useLogName('Comments');
    }

    /**
     * Check if the comment is deleted
     */
    public function isDeleted(): bool
    {
        return $this->status === 'deleted';
    }

    /**
     * Get the deletion timestamp, falling back to updated_at if deleted_at is null
     */
    public function getDeletionTimestampAttribute()
    {
        if ($this->isDeleted()) {
            return $this->deleted_at ?? $this->updated_at;
        }

        return null;
    }

    /**
     * Get the deleted message for this comment
     */
    public function getDeletedMessageAttribute(): string
    {
        return ($this->user->username ?? 'Unknown user') . ' has deleted this comment';
    }

    /**
     * Get the rendered comment
     */
    public function getRenderedCommentAttribute(): string
    {
        // If comment is deleted, return the deleted message
        if ($this->isDeleted()) {
            return $this->deleted_message;
        }

        $html = $this->comment ?? '';

        // Resolve mentioned users from stored IDs
        $mentionIds = is_array($this->mentions) ? $this->mentions : [];
        $mentionUsers = empty($mentionIds)
            ? collect()
            : User::query()->whereIn('id', $mentionIds)->get(['username', 'name']);

        // Build alternatives to match exactly (prefer longer names first)
        $terms = [];

        // Add @Everyone if it's in mentions
        if (in_array('@Everyone', $mentionIds)) {
            $terms[] = '@Everyone';
        }

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
