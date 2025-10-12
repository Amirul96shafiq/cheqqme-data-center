<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ChangelogHelper
{
    /**
     * Get paginated changelog entries from git commits
     */
    public static function getPaginatedChangelog(int $perPage = 10, int $page = 1): LengthAwarePaginator
    {
        try {
            // Get total commit count
            exec('git rev-list --count HEAD', $totalOutput);
            $total = (int) ($totalOutput[0] ?? 0);
            
            if ($total === 0) {
                return new LengthAwarePaginator(collect(), 0, $perPage, $page);
            }
            
            // Calculate offset
            $offset = ($page - 1) * $perPage;
            
            // Get commits with detailed format: hash|full_hash|date|author_name|author_email|message
            $format = '%h|%H|%ci|%an|%ae|%s';
            $command = sprintf('git log --pretty=format:"%s" --skip=%d -n %d', $format, $offset, $perPage);
            
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0 || empty($output)) {
                return new LengthAwarePaginator(collect(), 0, $perPage, $page);
            }
            
            $commits = collect($output)->map(function ($line) {
                [$shortHash, $fullHash, $date, $authorName, $authorEmail, $message] = explode('|', $line, 6);
                
                return [
                    'short_hash' => $shortHash,
                    'full_hash' => $fullHash,
                    'date' => \Carbon\Carbon::parse($date),
                    'author_name' => $authorName,
                    'author_email' => $authorEmail,
                    'author_avatar' => self::getGravatarUrl($authorEmail),
                    'message' => $message,
                ];
            });
            
            // Create paginator
            $paginator = new LengthAwarePaginator(
                $commits,
                $total,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
            
            // Add custom pagination links
            $paginator->withQueryString();
            
            return $paginator;
            
        } catch (\Exception $e) {
            \Log::error('Failed to fetch git changelog: ' . $e->getMessage());
            return new LengthAwarePaginator(collect(), 0, $perPage, $page);
        }
    }
    
    /**
     * Get Gravatar URL for email
     */
    protected static function getGravatarUrl(string $email, int $size = 32): string
    {
        $hash = md5(strtolower(trim($email)));
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d=identicon";
    }
    
}
