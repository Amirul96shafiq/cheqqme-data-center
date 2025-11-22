<?php

namespace App\Helpers;

class GitHelper
{
    /**
     * Cached version string to avoid running git commands on every request.
     */
    protected static ?string $cachedVersion = null;

    /**
     * Get the latest commit SHA from git
     */
    public static function getLatestCommitSha(): string
    {
        try {
            $commitHash = trim(exec('git rev-parse --short HEAD'));

            return $commitHash ?: 'unknown';
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    /**
     * Get the total commit count from git
     */
    public static function getCommitCount(): string
    {
        try {
            $commitCount = trim(exec('git rev-list --count HEAD'));

            return $commitCount ?: '0000';
        } catch (\Exception $e) {
            return '0000';
        }
    }

    /**
     * Get the latest commit SHA with custom prefix + postfix
     */
    public static function getVersionString(string $prefix = 'v0.3A_', string $suffix = '_local'): string
    {
        // Return cached value if we've already computed it for this PHP process.
        if (self::$cachedVersion !== null) {
            return self::$cachedVersion;
        }

        // If an explicit version string is configured, prefer that and avoid git calls entirely.
        if (function_exists('config')) {
            $configured = config('app.git_version');

            if (! empty($configured)) {
                self::$cachedVersion = $configured;

                return self::$cachedVersion;
            }
        }

        $commitSha = self::getLatestCommitSha();
        $commitCount = self::getCommitCount();

        // Format commit count as 4-digit number with 'c' suffix (e.g., "0760c")
        $formattedCommitCount = str_pad($commitCount, 4, '0', STR_PAD_LEFT).'c';

        $versionBase = $prefix.$formattedCommitCount.'_';

        self::$cachedVersion = $commitSha !== 'unknown'
            ? $versionBase.$commitSha.$suffix
            : $versionBase.'000000'.$suffix;

        return self::$cachedVersion;
    }
}
