<?php

namespace App\Helpers;

class SidebarAbbreviationHelper
{
    /**
     * Predefined abbreviations for common group and resource names
     */
    private static array $predefinedAbbreviations = [
        // Groups
        'Boards' => 'B.',
        'Data Management' => 'D.',
        'User Management' => 'U.',
        'Tools' => 'T.',
        'Settings' => 'S.',
        'Reports' => 'R.',
        'Analytics' => 'A.',
        'Administration' => 'A.',

        // Resources
        'Clients' => 'C.',
        'Users' => 'U.',
        'Projects' => 'P.',
        'Documents' => 'D.',
        'Important URLs' => 'URLs',
        'Phone Numbers' => 'Ph.',
        'Trello Boards' => 'T.',
        'Activity Logs' => 'A.',
        'Action Board' => 'A.',
        'Projects Trello Board' => 'P.',
    ];

    /**
     * Generate abbreviation for a given label
     */
    public static function generateAbbreviation(string $label, int $maxLength = 4): string
    {
        // Check if we have a predefined abbreviation
        if (isset(self::$predefinedAbbreviations[$label])) {
            return self::$predefinedAbbreviations[$label];
        }

        // Generate abbreviation automatically
        return self::autoGenerateAbbreviation($label, $maxLength);
    }

    /**
     * Automatically generate abbreviation from label
     */
    private static function autoGenerateAbbreviation(string $label, int $maxLength): string
    {
        // Remove common words that don't add meaning
        $commonWords = ['the', 'and', 'or', 'of', 'in', 'on', 'at', 'to', 'for', 'with', 'by'];
        $words = explode(' ', strtolower($label));
        $meaningfulWords = array_filter($words, fn ($word) => ! in_array($word, $commonWords));

        // If only one meaningful word, take first few characters
        if (count($meaningfulWords) === 1) {
            $word = reset($meaningfulWords);

            return strtoupper(substr($word, 0, min($maxLength, strlen($word)))).'.';
        }

        // Multiple words - take first letter of each meaningful word
        $abbreviation = '';
        foreach ($meaningfulWords as $word) {
            if (strlen($abbreviation) >= $maxLength) {
                break;
            }
            $abbreviation .= strtoupper(substr($word, 0, 1));
        }

        // Add period if abbreviation is short
        if (strlen($abbreviation) <= 3) {
            $abbreviation .= '.';
        }

        return $abbreviation;
    }

    /**
     * Add or update a predefined abbreviation
     */
    public static function addAbbreviation(string $label, string $abbreviation): void
    {
        self::$predefinedAbbreviations[$label] = $abbreviation;
    }

    /**
     * Get all predefined abbreviations
     */
    public static function getPredefinedAbbreviations(): array
    {
        return self::$predefinedAbbreviations;
    }
}
