<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageOptimizationService
{
    /**
     * Generate optimized thumbnail for Kanban card display
     */
    public function generateKanbanThumbnail(string $imagePath, int $width = 400, int $height = 128): ?string
    {
        try {
            if (! Storage::disk('public')->exists($imagePath)) {
                return null;
            }

            $fullPath = Storage::disk('public')->path($imagePath);
            $pathInfo = pathinfo($imagePath);
            $thumbnailPath = $pathInfo['dirname'].'/thumbnails/'.$pathInfo['filename'].'_kanban.'.$pathInfo['extension'];

            // Create thumbnails directory if it doesn't exist
            $thumbnailDir = Storage::disk('public')->path(dirname($thumbnailPath));
            if (! is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Generate thumbnail with proper aspect ratio and higher quality
            $manager = new ImageManager(new Driver);
            $image = $manager->read($fullPath)
                ->scaleDown($width, $height) // Preserve aspect ratio, no cropping
                ->toJpeg(95); // Higher quality for better clarity

            Storage::disk('public')->put($thumbnailPath, $image);

            return $thumbnailPath;
        } catch (\Exception $e) {
            \Log::warning('Failed to generate Kanban thumbnail: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Generate medium-sized image for general display
     */
    public function generateMediumImage(string $imagePath, int $width = 800, int $height = 600): ?string
    {
        try {
            if (! Storage::disk('public')->exists($imagePath)) {
                return null;
            }

            $fullPath = Storage::disk('public')->path($imagePath);
            $pathInfo = pathinfo($imagePath);
            $mediumPath = $pathInfo['dirname'].'/medium/'.$pathInfo['filename'].'_medium.'.$pathInfo['extension'];

            // Create medium directory if it doesn't exist
            $mediumDir = Storage::disk('public')->path(dirname($mediumPath));
            if (! is_dir($mediumDir)) {
                mkdir($mediumDir, 0755, true);
            }

            // Generate medium image
            $manager = new ImageManager(new Driver);
            $image = $manager->read($fullPath)
                ->scaleDown($width, $height)
                ->toJpeg(90); // Higher quality for medium images

            Storage::disk('public')->put($mediumPath, $image);

            return $mediumPath;
        } catch (\Exception $e) {
            \Log::warning('Failed to generate medium image: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Get optimized image URL, generating thumbnail if needed
     */
    public function getOptimizedImageUrl(string $imagePath, string $size = 'kanban'): ?string
    {
        if (! $imagePath) {
            return null;
        }

        $pathInfo = pathinfo($imagePath);
        $thumbnailPath = null;

        switch ($size) {
            case 'kanban':
                $thumbnailPath = $pathInfo['dirname'].'/thumbnails/'.$pathInfo['filename'].'_kanban.'.$pathInfo['extension'];
                if (! Storage::disk('public')->exists($thumbnailPath)) {
                    $thumbnailPath = $this->generateKanbanThumbnail($imagePath);
                }
                break;
            case 'medium':
                $thumbnailPath = $pathInfo['dirname'].'/medium/'.$pathInfo['filename'].'_medium.'.$pathInfo['extension'];
                if (! Storage::disk('public')->exists($thumbnailPath)) {
                    $thumbnailPath = $this->generateMediumImage($imagePath);
                }
                break;
            default:
                return asset('storage/'.$imagePath);
        }

        return $thumbnailPath ? asset('storage/'.$thumbnailPath) : asset('storage/'.$imagePath);
    }

    /**
     * Clean up old thumbnails when original image is deleted
     */
    public function cleanupThumbnails(string $imagePath): void
    {
        if (! $imagePath) {
            return;
        }

        $pathInfo = pathinfo($imagePath);
        $thumbnailPaths = [
            $pathInfo['dirname'].'/thumbnails/'.$pathInfo['filename'].'_kanban.'.$pathInfo['extension'],
            $pathInfo['dirname'].'/medium/'.$pathInfo['filename'].'_medium.'.$pathInfo['extension'],
        ];

        foreach ($thumbnailPaths as $thumbnailPath) {
            if (Storage::disk('public')->exists($thumbnailPath)) {
                Storage::disk('public')->delete($thumbnailPath);
            }
        }
    }
}
