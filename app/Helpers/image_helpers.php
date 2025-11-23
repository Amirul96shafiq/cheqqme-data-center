<?php

use App\Services\ImageOptimizationService;

/**
 * Get optimized public image URL that automatically serves WebP versions when available
 *
 * @param  string  $imagePath  Path to the image file relative to public directory (e.g., 'images/bg.png')
 * @param  bool  $convertIfMissing  Whether to convert to WebP if WebP version doesn't exist
 * @return string Optimized image URL
 */
if (! function_exists('optimized_image_url')) {
    function optimized_image_url(string $imagePath, bool $convertIfMissing = false): string
    {
        return ImageOptimizationService::getCachedPublicImageUrl($imagePath, $convertIfMissing);
    }
}

/**
 * Get optimized asset URL that automatically serves WebP versions when available
 *
 * @param  string  $assetPath  Path to the asset relative to public directory
 * @param  bool  $convertIfMissing  Whether to convert to WebP if WebP version doesn't exist
 * @return string Optimized asset URL
 */
if (! function_exists('optimized_asset')) {
    function optimized_asset(string $assetPath, bool $convertIfMissing = false): string
    {
        // Only optimize if it's an image in the images or logos directory
        if ((str_starts_with($assetPath, 'images/') || str_starts_with($assetPath, 'logos/')) &&
            in_array(pathinfo($assetPath, PATHINFO_EXTENSION), ['png', 'jpg', 'jpeg'])) {
            return optimized_image_url($assetPath, $convertIfMissing);
        }

        // For non-image assets, return regular asset URL
        return asset($assetPath);
    }
}
