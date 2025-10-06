<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use App\Notifications\UserMentionedInComment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class UserMentionService
{
    /**
     * Extract mentions from comment text and return user IDs
     */
    public function extractMentions(string $commentText): array
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
            if (! $parts) {
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

        // Query users by exact username or exact display name with caching
        $users = $this->getUsersForMentionMatching($candidates);

        if ($users->isEmpty()) {
            return $mentions;
        }

        // Build lookup maps for fast resolution
        $byUsername = [];
        $byName = [];
        foreach ($users as $user) {
            if (! empty($user->username)) {
                $byUsername[strtolower($user->username)] = $user->id;
            }
            if (! empty($user->name)) {
                $byName[strtolower($user->name)] = $user->id;
            }
        }

        // Resolve mentions by trying longest variants first
        $resolvedIds = [];
        foreach ($occurrences as $occurrence) {
            foreach ($occurrence['variants'] as $variant) {
                $lowerVariant = strtolower($variant);
                if (isset($byUsername[$lowerVariant])) {
                    $resolvedIds[] = $byUsername[$lowerVariant];
                    break; // Found match, move to next occurrence
                } elseif (isset($byName[$lowerVariant])) {
                    $resolvedIds[] = $byName[$lowerVariant];
                    break; // Found match, move to next occurrence
                }
            }
        }

        // Remove duplicates and add to mentions
        $mentions = array_merge($mentions, array_unique($resolvedIds));

        return $mentions;
    }

    /**
     * Get users for mention matching with caching
     */
    protected function getUsersForMentionMatching(array $candidates): Collection
    {
        // Use cache to avoid repeated database queries
        static $userCache = [];
        $cacheKey = md5(serialize($candidates));

        if (isset($userCache[$cacheKey])) {
            return $userCache[$cacheKey];
        }

        $users = User::query()
            ->whereIn('username', $candidates)
            ->orWhereIn('name', $candidates)
            ->get(['id', 'username', 'name']);

        $userCache[$cacheKey] = $users;

        // Limit cache size to prevent memory issues
        if (count($userCache) > 100) {
            $userCache = array_slice($userCache, -50, null, true);
        }

        return $users;
    }

    /**
     * Process mentions in a comment and send notifications
     */
    public function processMentions(Comment $comment): void
    {
        try {
            Log::info('🚀 UserMentionService::processMentions called', [
                'commentId' => $comment->id,
                'mentions' => $comment->mentions,
                'mentionsType' => gettype($comment->mentions),
                'isArray' => is_array($comment->mentions),
                'hasMentions' => ! empty($comment->mentions),
            ]);

            if (! $comment->mentions || ! is_array($comment->mentions)) {
                Log::warning('❌ No mentions to process', [
                    'mentions' => $comment->mentions,
                    'isArray' => is_array($comment->mentions),
                ]);

                return;
            }

            // Validate mentions array
            $validMentions = $this->validateMentions($comment->mentions);
            if (empty($validMentions)) {
                Log::warning('❌ No valid mentions found after validation', [
                    'originalMentions' => $comment->mentions,
                ]);

                return;
            }

            // Check if @Everyone is mentioned
            if (in_array('@Everyone', $validMentions)) {
                $this->processEveryoneMention($comment);

                return; // Exit early since @Everyone covers everyone
            }

            $this->processRegularMentions($comment, $validMentions);
        } catch (\Exception $e) {
            Log::error('❌ Error processing mentions', [
                'commentId' => $comment->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Validate mentions array
     */
    protected function validateMentions(array $mentions): array
    {
        $validMentions = [];

        foreach ($mentions as $mention) {
            // Handle @Everyone special case
            if ($mention === '@Everyone') {
                $validMentions[] = $mention;

                continue;
            }

            // Validate user ID mentions
            if (is_numeric($mention) && $mention > 0) {
                $validMentions[] = (int) $mention;
            } elseif (is_string($mention) && ! empty(trim($mention))) {
                // Handle string mentions (should be converted to user IDs)
                Log::warning('String mention found, should be converted to user ID', [
                    'mention' => $mention,
                ]);
            }
        }

        return array_unique($validMentions);
    }

    /**
     * Process @Everyone mention
     */
    protected function processEveryoneMention(Comment $comment): void
    {
        Log::info('👥 Processing @Everyone mention');

        // Get all users including the comment author (consistent with regular mentions)
        $allUsers = User::all();

        Log::info('👥 Users to notify', [
            'userCount' => $allUsers->count(),
            'users' => $allUsers->pluck('username')->toArray(),
            'commentAuthorId' => $comment->user_id,
        ]);

        foreach ($allUsers as $user) {
            Log::info('📧 Sending notification to user', [
                'userId' => $user->id,
                'username' => $user->username,
            ]);

            try {
                // Use UserMentionedInComment notification class
                $user->notify(new UserMentionedInComment($comment, $comment->task, $comment->user));
                Log::info('✅ Notification sent successfully', [
                    'userId' => $user->id,
                    'username' => $user->username,
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Failed to send notification', [
                    'userId' => $user->id,
                    'username' => $user->username,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('✅ @Everyone notifications processing completed');
    }

    /**
     * Process regular user mentions
     */
    protected function processRegularMentions(Comment $comment, array $validMentions): void
    {
        Log::info('👤 Processing regular user mentions', [
            'mentionIds' => $validMentions,
        ]);

        // Regular user mentions
        $mentionedUsers = User::whereIn('id', $validMentions)->get();

        foreach ($mentionedUsers as $user) {
            Log::info('📧 Sending notification to mentioned user', [
                'userId' => $user->id,
                'username' => $user->username,
            ]);

            try {
                // Use UserMentionedInComment notification class
                $user->notify(new UserMentionedInComment($comment, $comment->task, $comment->user));
                Log::info('✅ Notification sent successfully', [
                    'userId' => $user->id,
                    'username' => $user->username,
                ]);
            } catch (\Exception $e) {
                Log::error('❌ Failed to send notification', [
                    'userId' => $user->id,
                    'username' => $user->username,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('✅ Regular mentions notifications processing completed');
    }

    /**
     * Get users for mention search
     */
    public function getUsersForMentionSearch(): Collection
    {
        $provider = new \Filament\AvatarProviders\UiAvatarsProvider;

        return User::query()
            ->select(['id', 'username', 'email', 'name', 'avatar'])
            ->orderBy('username')
            ->limit(500)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'default_avatar' => $provider->get($user),
            ]);
    }

    /**
     * Render comment with mention highlighting
     */
    public function renderCommentWithMentions(Comment $comment): string
    {
        // If comment is deleted, return the deleted message
        if ($comment->isDeleted()) {
            return $comment->deleted_message;
        }

        $html = $comment->comment ?? '';

        // Resolve mentioned users from stored IDs
        $mentionIds = is_array($comment->mentions) ? $comment->mentions : [];
        $mentionUsers = empty($mentionIds)
            ? collect()
            : User::query()->whereIn('id', $mentionIds)->get(['username', 'name']);

        // Build alternatives to match exactly (prefer longer names first)
        $terms = [];

        // Add @Everyone if it's in mentions
        if (in_array('@Everyone', $mentionIds)) {
            $terms[] = '@Everyone';
        }

        foreach ($mentionUsers as $user) {
            if (! empty($user->name)) {
                $terms[] = '@'.$user->name;
            }
            if (! empty($user->username)) {
                $terms[] = '@'.$user->username;
            }
        }

        // Deduplicate and sort by length desc to avoid partial overlaps
        $terms = array_values(array_unique(array_filter($terms, static fn ($t) => $t !== '')));
        usort($terms, static fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        // If no known terms, fall back to original content
        if (empty($terms)) {
            return $html;
        }

        // Escape terms for regex
        $escapedTerms = array_map(static fn ($t) => preg_quote($t, '/'), $terms);
        $alternation = implode('|', $escapedTerms);

        $parts = preg_split('/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $inside = ['code' => 0, 'pre' => 0, 'a' => 0];
        $out = '';

        // Process parts
        foreach ($parts as $part) {
            if (preg_match('/^<[^>]*>$/', $part)) {
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
                    $out .= preg_replace_callback('/('.$alternation.')(?=[\s\.,;:!\?\)]|$|<)/u', function ($m) {
                        $text = $m[1]; // e.g., @Full Name or @username

                        return '<span class="mention">'.htmlspecialchars($text, ENT_QUOTES, 'UTF-8').'</span>';
                    }, $part);
                }
            }
        }

        return $out;
    }

    /**
     * Get mentioned users from a comment
     */
    public function getMentionedUsers(Comment $comment): Collection
    {
        if (! $comment->mentions || ! is_array($comment->mentions)) {
            return collect();
        }

        return User::whereIn('id', $comment->mentions)->get();
    }
}
