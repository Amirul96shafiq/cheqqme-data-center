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
        return app(\App\Services\UserMentionService::class)->getMentionedUsers($this);
    }

    /**
     * Process mentions in comment text and send notifications
     */
    public function processMentions()
    {
        app(\App\Services\UserMentionService::class)->processMentions($this);
    }

    /**
     * Extract user mentions from comment text
     */
    public static function extractMentions(string $commentText): array
    {
        return app(\App\Services\UserMentionService::class)->extractMentions($commentText);
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
        return ($this->user->username ?? 'Unknown user').' has deleted this comment';
    }

    /**
     * Get the rendered comment
     */
    public function getRenderedCommentAttribute(): string
    {
        return app(\App\Services\UserMentionService::class)->renderCommentWithMentions($this);
    }
}
