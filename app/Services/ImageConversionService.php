<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\TemporaryUploadedFile;

/**
 * Image Conversion Service
 *
 * Handles automatic conversion of images to WebP format for better compression
 * and reduced storage size.
 */
class ImageConversionService
{
    /**
     * Convert an image file to WebP format
     *
     * @param  string  $filePath  Path to the image file (relative to storage disk)
     * @param  int  $quality  WebP quality (0-100, default: 85)
     * @return string|null New file path if conversion successful, null if failed
     */
    public function convertToWebp(string $filePath, int $quality = 85): ?string
    {
        try {
            // Check if file exists
            if (! Storage::disk('public')->exists($filePath)) {
                return null;
            }

            // Skip if already WebP
            if ($this->isWebpFile($filePath)) {
                return $filePath;
            }

            // Check if it's an image file
            if (! $this->isImageFile($filePath)) {
                return $filePath; // Return original path for non-images
            }

            $fullPath = Storage::disk('public')->path($filePath);

            // Generate new file path with .webp extension
            $pathInfo = pathinfo($filePath);
            $newPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'.webp';

            // Convert using Intervention Image
            $manager = new ImageManager(new Driver);
            $image = $manager->read($fullPath);
            $convertedImage = $image->toWebp($quality);

            // Save the converted image
            Storage::disk('public')->put($newPath, $convertedImage);

            // Delete the original file
            Storage::disk('public')->delete($filePath);

            return $newPath;
        } catch (\Exception $e) {
            \Log::warning('Failed to convert image to WebP: '.$e->getMessage(), [
                'file_path' => $filePath,
                'quality' => $quality,
            ]);

            return null;
        }
    }

    /**
     * Convert a Livewire TemporaryUploadedFile to WebP format
     *
     * @param  TemporaryUploadedFile  $file  The uploaded file
     * @param  int  $quality  WebP quality (0-100, default: 85)
     * @return bool True if conversion successful
     */
    public function convertTemporaryFile(TemporaryUploadedFile $file, int $quality = 85): bool
    {
        try {
            // Skip if already WebP
            if ($this->isWebpMimeType($file->getMimeType())) {
                return true;
            }

            // Skip if not an image
            if (! $this->isImageMimeType($file->getMimeType())) {
                return true; // Not an error, just skip conversion
            }

            $manager = new ImageManager(new Driver);
            $image = $manager->read($file->getRealPath());
            $convertedImage = $image->toWebp($quality);

            // Overwrite the original file
            file_put_contents($file->getRealPath(), $convertedImage);

            // Update the file extension in the temporary file
            $originalName = $file->getClientOriginalName();
            $pathInfo = pathinfo($originalName);

            if (isset($pathInfo['extension'])) {
                $newName = $pathInfo['filename'].'.webp';

                // Update the temporary file's name property
                $reflection = new \ReflectionClass($file);
                $nameProperty = $reflection->getProperty('name');
                $nameProperty->setAccessible(true);
                $nameProperty->setValue($file, $newName);
            }

            return true;
        } catch (\Exception $e) {
            \Log::warning('Failed to convert temporary file to WebP: '.$e->getMessage(), [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'quality' => $quality,
            ]);

            return false;
        }
    }

    /**
     * Check if a file path represents an image file
     *
     * @param  string  $filePath  Path to the file (relative to storage disk)
     * @return bool True if file is an image
     */
    public function isImageFile(string $filePath): bool
    {
        try {
            $fullPath = Storage::disk('public')->path($filePath);
            $mimeType = mime_content_type($fullPath);

            return $this->isImageMimeType($mimeType);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if a MIME type represents an image
     *
     * @param  string  $mimeType  MIME type to check
     * @return bool True if MIME type is an image
     */
    public function isImageMimeType(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    /**
     * Check if a file is already in WebP format
     *
     * @param  string  $filePath  Path to the file (relative to storage disk)
     * @return bool True if file is WebP
     */
    public function isWebpFile(string $filePath): bool
    {
        try {
            $fullPath = Storage::disk('public')->path($filePath);
            $mimeType = mime_content_type($fullPath);

            return $this->isWebpMimeType($mimeType);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if a MIME type is WebP
     *
     * @param  string  $mimeType  MIME type to check
     * @return bool True if MIME type is WebP
     */
    public function isWebpMimeType(string $mimeType): bool
    {
        return $mimeType === 'image/webp';
    }

    /**
     * Get the MIME type of a file
     *
     * @param  string  $filePath  Path to the file (relative to storage disk)
     * @return string|null MIME type or null if error
     */
    public function getImageMimeType(string $filePath): ?string
    {
        try {
            $fullPath = Storage::disk('public')->path($filePath);

            return mime_content_type($fullPath);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Convert a public image file to WebP format
     *
     * @param  string  $imagePath  Path to the image file relative to public directory (e.g., 'images/bg.png')
     * @param  int  $quality  WebP quality (0-100, default: 85)
     * @return string|null New file path if conversion successful, null if failed
     */
    public function convertPublicImageToWebp(string $imagePath, int $quality = 85): ?string
    {
        try {
            $fullPath = public_path($imagePath);

            // Check if file exists
            if (! file_exists($fullPath)) {
                return null;
            }

            // Skip if already WebP
            $mimeType = mime_content_type($fullPath);
            if ($mimeType === 'image/webp') {
                return $imagePath;
            }

            // Skip if not an image or not supported
            if (! $this->isImageMimeType($mimeType) || ! $this->isSupportedForWebpConversion($mimeType)) {
                return $imagePath; // Return original path for non-images or unsupported formats
            }

            // Generate new file path with .webp extension
            $pathInfo = pathinfo($imagePath);
            $newPath = $pathInfo['dirname'].'/'.$pathInfo['filename'].'.webp';
            $newFullPath = public_path($newPath);

            // Convert using Intervention Image
            $manager = new ImageManager(new Driver);
            $image = $manager->read($fullPath);
            $convertedImage = $image->toWebp($quality);

            // Save the converted image
            file_put_contents($newFullPath, $convertedImage);

            return $newPath;
        } catch (\Exception $e) {
            \Log::warning('Failed to convert public image to WebP: '.$e->getMessage(), [
                'image_path' => $imagePath,
                'quality' => $quality,
            ]);

            return null;
        }
    }

    /**
     * Get the optimized public image URL, serving WebP when available
     *
     * @param  string  $imagePath  Path to the image file relative to public directory (e.g., 'images/bg.png')
     * @param  bool  $convertIfMissing  Whether to convert to WebP if WebP version doesn't exist
     * @return string URL to the optimized image
     */
    public function getOptimizedPublicImageUrl(string $imagePath, bool $convertIfMissing = false): string
    {
        $webpPath = $this->getWebpPath($imagePath);
        $webpFullPath = public_path($webpPath);

        // If WebP version exists, serve it
        if (file_exists($webpFullPath)) {
            return asset($webpPath);
        }

        // If WebP version doesn't exist and we should convert it
        if ($convertIfMissing) {
            $convertedPath = $this->convertPublicImageToWebp($imagePath, 85);
            if ($convertedPath && $convertedPath !== $imagePath) {
                return asset($convertedPath);
            }
        }

        // Fallback to original image
        return asset($imagePath);
    }

    /**
     * Get the WebP version path for a given image path
     *
     * @param  string  $imagePath  Original image path
     * @return string WebP version path
     */
    public function getWebpPath(string $imagePath): string
    {
        $pathInfo = pathinfo($imagePath);

        return $pathInfo['dirname'].'/'.$pathInfo['filename'].'.webp';
    }

    /**
     * Check if an image format is supported for WebP conversion
     *
     * @param  string  $mimeType  MIME type to check
     * @return bool True if format is supported
     */
    public function isSupportedForWebpConversion(string $mimeType): bool
    {
        $supportedFormats = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/bmp',
        ];

        return in_array($mimeType, $supportedFormats);
    }
}
