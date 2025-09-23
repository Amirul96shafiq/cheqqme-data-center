<?php

namespace App\Helpers;

class GitHelper
{
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
     * Get the latest commit SHA with custom prefix + postfix
     */
    public static function getVersionString(string $prefix = 'v0.3alpha_', string $suffix = '_local'): string
    {
        $commitSha = self::getLatestCommitSha();

        return $commitSha !== 'unknown' ? $prefix.$commitSha.$suffix : $prefix.'000000'.$suffix;
    }
}
