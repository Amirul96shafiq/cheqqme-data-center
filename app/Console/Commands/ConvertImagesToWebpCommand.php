<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\Task;
use App\Models\User;
use App\Services\ImageConversionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Convert existing images to WebP format for better compression and storage efficiency.
 *
 * This command processes existing images in the database and converts them to WebP format,
 * updating the database records with the new file paths.
 */
class ConvertImagesToWebpCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:convert-to-webp
                            {--dry-run : Preview changes without actually converting files}
                            {--quality=85 : WebP quality (0-100, default: 85)}
                            {--batch-size=50 : Number of records to process at once}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existing images to WebP format for better compression (uploaded files and public images)';

    /**
     * Image conversion service instance.
     */
    protected ImageConversionService $conversionService;

    /**
     * Statistics for reporting.
     */
    protected array $stats = [
        'processed' => 0,
        'converted' => 0,
        'skipped' => 0,
        'errors' => 0,
        'space_saved' => 0,
    ];

    /**
     * Create a new command instance.
     */
    public function __construct(ImageConversionService $conversionService)
    {
        parent::__construct();
        $this->conversionService = $conversionService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $quality = (int) $this->option('quality');
        $batchSize = (int) $this->option('batch-size');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No files will be modified');
        }

        $this->info("🎯 Starting WebP conversion (quality: {$quality}%, batch size: {$batchSize})");
        $this->newLine();

        $startTime = microtime(true);

        // Convert user avatars
        $this->convertUserAvatars($quality, $batchSize, $dryRun);

        // Convert user cover images
        $this->convertUserCoverImages($quality, $batchSize, $dryRun);

        // Convert document images
        $this->convertDocumentImages($quality, $batchSize, $dryRun);

        // Convert task attachments
        $this->convertTaskAttachments($quality, $batchSize, $dryRun);

        // Convert public images
        $this->convertPublicImages($quality, $dryRun);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->displaySummary($duration, $dryRun);

        return self::SUCCESS;
    }

    /**
     * Convert user avatars to WebP.
     */
    protected function convertUserAvatars(int $quality, int $batchSize, bool $dryRun): void
    {
        $this->info('📸 Converting user avatars...');

        User::whereNotNull('avatar')
            ->chunk($batchSize, function ($users) use ($quality, $dryRun) {
                foreach ($users as $user) {
                    $this->convertUserImage($user, 'avatar', $quality, $dryRun);
                }
            });
    }

    /**
     * Convert user cover images to WebP.
     */
    protected function convertUserCoverImages(int $quality, int $batchSize, bool $dryRun): void
    {
        $this->info('🖼️  Converting user cover images...');

        User::whereNotNull('cover_image')
            ->chunk($batchSize, function ($users) use ($quality, $dryRun) {
                foreach ($users as $user) {
                    $this->convertUserImage($user, 'cover_image', $quality, $dryRun);
                }
            });
    }

    /**
     * Convert a user's image field to WebP.
     */
    protected function convertUserImage(User $user, string $field, int $quality, bool $dryRun): void
    {
        $this->stats['processed']++;

        $imagePath = $user->$field;

        if (! $imagePath) {
            $this->stats['skipped']++;

            return;
        }

        // Skip if already WebP
        if ($this->conversionService->isWebpFile($imagePath)) {
            $this->stats['skipped']++;

            return;
        }

        // Skip if not an image
        if (! $this->conversionService->isImageFile($imagePath)) {
            $this->stats['skipped']++;

            return;
        }

        if ($dryRun) {
            $this->line("  Would convert: {$imagePath}");
            $this->stats['converted']++;

            return;
        }

        try {
            $originalSize = Storage::disk('public')->size($imagePath);
            $newPath = $this->conversionService->convertToWebp($imagePath, $quality);

            if ($newPath) {
                // Update database record
                $user->update([$field => $newPath]);

                $newSize = Storage::disk('public')->size($newPath);
                $savedBytes = $originalSize - $newSize;
                $this->stats['space_saved'] += $savedBytes;
                $this->stats['converted']++;

                $this->line("  ✅ Converted: {$imagePath} → {$newPath} (saved ".$this->formatBytes($savedBytes).')');
            } else {
                $this->stats['errors']++;
                $this->error("  ❌ Failed to convert: {$imagePath}");
            }
        } catch (\Exception $e) {
            $this->stats['errors']++;
            $this->error("  ❌ Error converting {$imagePath}: {$e->getMessage()}");
        }
    }

    /**
     * Convert document images to WebP.
     */
    protected function convertDocumentImages(int $quality, int $batchSize, bool $dryRun): void
    {
        $this->info('📄 Converting document images...');

        Document::whereNotNull('file_path')
            ->where('type', 'internal')
            ->chunk($batchSize, function ($documents) use ($quality, $dryRun) {
                foreach ($documents as $document) {
                    $this->convertDocumentImage($document, $quality, $dryRun);
                }
            });
    }

    /**
     * Convert a document's image to WebP.
     */
    protected function convertDocumentImage(Document $document, int $quality, bool $dryRun): void
    {
        $this->stats['processed']++;

        $imagePath = $document->file_path;

        if (! $imagePath) {
            $this->stats['skipped']++;

            return;
        }

        // Skip if already WebP
        if ($this->conversionService->isWebpFile($imagePath)) {
            $this->stats['skipped']++;

            return;
        }

        // Skip if not an image
        if (! $this->conversionService->isImageFile($imagePath)) {
            $this->stats['skipped']++;

            return;
        }

        if ($dryRun) {
            $this->line("  Would convert: {$imagePath}");
            $this->stats['converted']++;

            return;
        }

        try {
            $originalSize = Storage::disk('public')->size($imagePath);
            $newPath = $this->conversionService->convertToWebp($imagePath, $quality);

            if ($newPath) {
                // Update database record
                $document->update(['file_path' => $newPath]);

                $newSize = Storage::disk('public')->size($newPath);
                $savedBytes = $originalSize - $newSize;
                $this->stats['space_saved'] += $savedBytes;
                $this->stats['converted']++;

                $this->line("  ✅ Converted: {$imagePath} → {$newPath} (saved ".$this->formatBytes($savedBytes).')');
            } else {
                $this->stats['errors']++;
                $this->error("  ❌ Failed to convert: {$imagePath}");
            }
        } catch (\Exception $e) {
            $this->stats['errors']++;
            $this->error("  ❌ Error converting {$imagePath}: {$e->getMessage()}");
        }
    }

    /**
     * Convert task attachment images to WebP.
     */
    protected function convertTaskAttachments(int $quality, int $batchSize, bool $dryRun): void
    {
        $this->info('📎 Converting task attachment images...');

        Task::whereNotNull('attachments')
            ->chunk($batchSize, function ($tasks) use ($quality, $dryRun) {
                foreach ($tasks as $task) {
                    $this->convertTaskAttachmentImages($task, $quality, $dryRun);
                }
            });
    }

    /**
     * Convert public images to WebP.
     */
    protected function convertPublicImages(int $quality, bool $dryRun): void
    {
        $this->info('🌐 Converting public images...');

        $publicImages = $this->getPublicImages();

        foreach ($publicImages as $imagePath) {
            $this->convertPublicImage($imagePath, $quality, $dryRun);
        }
    }

    /**
     * Get all public images that should be converted.
     */
    protected function getPublicImages(): array
    {
        $publicImages = [];

        // Scan public/images directory recursively
        $this->scanDirectoryForImages('images', $publicImages);

        // Scan public/logos directory recursively
        $this->scanDirectoryForImages('logos', $publicImages);

        return $publicImages;
    }

    /**
     * Recursively scan directory for image files.
     */
    protected function scanDirectoryForImages(string $directory, array &$images): void
    {
        $fullPath = public_path($directory);

        if (! is_dir($fullPath)) {
            return;
        }

        $files = scandir($fullPath);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $directory.'/'.$file;
            $fullFilePath = public_path($filePath);

            if (is_dir($fullFilePath)) {
                // Recursively scan subdirectories
                $this->scanDirectoryForImages($filePath, $images);
            } elseif ($this->isImageFile($fullFilePath)) {
                $images[] = $filePath;
            }
        }
    }

    /**
     * Check if a file is an image based on its extension and MIME type.
     */
    protected function isImageFile(string $filePath): bool
    {
        if (! file_exists($filePath)) {
            return false;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp'];

        if (! in_array($extension, $imageExtensions)) {
            return false;
        }

        $mimeType = mime_content_type($filePath);

        return $this->conversionService->isImageMimeType($mimeType) &&
               $this->conversionService->isSupportedForWebpConversion($mimeType);
    }

    /**
     * Convert a public image to WebP.
     */
    protected function convertPublicImage(string $imagePath, int $quality, bool $dryRun): void
    {
        $this->stats['processed']++;

        // Skip if already WebP
        if ($this->conversionService->isWebpFile($imagePath)) {
            $this->stats['skipped']++;

            return;
        }

        // Skip if not an image
        if (! $this->isImageFile(public_path($imagePath))) {
            $this->stats['skipped']++;

            return;
        }

        if ($dryRun) {
            $this->line("  Would convert: {$imagePath}");
            $this->stats['converted']++;

            return;
        }

        try {
            $originalSize = filesize(public_path($imagePath));
            $newPath = $this->conversionService->convertPublicImageToWebp($imagePath, $quality);

            if ($newPath) {
                $newSize = filesize(public_path($newPath));
                $savedBytes = $originalSize - $newSize;
                $this->stats['space_saved'] += $savedBytes;
                $this->stats['converted']++;

                $this->line("  ✅ Converted: {$imagePath} → {$newPath} (saved ".$this->formatBytes($savedBytes).')');
            } else {
                $this->stats['errors']++;
                $this->error("  ❌ Failed to convert: {$imagePath}");
            }
        } catch (\Exception $e) {
            $this->stats['errors']++;
            $this->error("  ❌ Error converting {$imagePath}: {$e->getMessage()}");
        }
    }

    /**
     * Convert images in task attachments to WebP.
     */
    protected function convertTaskAttachmentImages(Task $task, int $quality, bool $dryRun): void
    {
        $attachments = $task->attachments;

        if (! is_array($attachments) || empty($attachments)) {
            return;
        }

        $updatedAttachments = [];
        $hasChanges = false;

        foreach ($attachments as $attachment) {
            $this->stats['processed']++;

            // Skip if already WebP
            if ($this->conversionService->isWebpFile($attachment)) {
                $this->stats['skipped']++;
                $updatedAttachments[] = $attachment;

                continue;
            }

            // Skip if not an image
            if (! $this->conversionService->isImageFile($attachment)) {
                $this->stats['skipped']++;
                $updatedAttachments[] = $attachment;

                continue;
            }

            if ($dryRun) {
                $this->line("  Would convert: {$attachment}");
                $this->stats['converted']++;
                $updatedAttachments[] = $attachment; // Keep original for dry run

                continue;
            }

            try {
                $originalSize = Storage::disk('public')->size($attachment);
                $newPath = $this->conversionService->convertToWebp($attachment, $quality);

                if ($newPath) {
                    $updatedAttachments[] = $newPath;
                    $hasChanges = true;

                    $newSize = Storage::disk('public')->size($newPath);
                    $savedBytes = $originalSize - $newSize;
                    $this->stats['space_saved'] += $savedBytes;
                    $this->stats['converted']++;

                    $this->line("  ✅ Converted: {$attachment} → {$newPath} (saved ".$this->formatBytes($savedBytes).')');
                } else {
                    $this->stats['errors']++;
                    $this->error("  ❌ Failed to convert: {$attachment}");
                    $updatedAttachments[] = $attachment; // Keep original on error
                }
            } catch (\Exception $e) {
                $this->stats['errors']++;
                $this->error("  ❌ Error converting {$attachment}: {$e->getMessage()}");
                $updatedAttachments[] = $attachment; // Keep original on error
            }
        }

        // Update task if there were changes
        if ($hasChanges && ! $dryRun) {
            $task->update(['attachments' => $updatedAttachments]);
        }
    }

    /**
     * Format bytes into human readable format.
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Display conversion summary.
     */
    protected function displaySummary(float $duration, bool $dryRun): void
    {
        $this->newLine();
        $this->info('📊 Conversion Summary:');

        if ($dryRun) {
            $this->line('  🔍 Dry run completed - no files were modified');
        }

        $this->line("  ⏱️  Duration: {$duration}s");
        $this->line("  📁 Files processed: {$this->stats['processed']}");
        $this->line("  ✅ Files converted: {$this->stats['converted']}");
        $this->line("  ⏭️  Files skipped: {$this->stats['skipped']}");
        $this->line("  ❌ Errors: {$this->stats['errors']}");

        if ($this->stats['space_saved'] > 0) {
            $this->line("  💾 Space saved: {$this->formatBytes($this->stats['space_saved'])}");
        }

        $this->newLine();

        if (! $dryRun && $this->stats['converted'] > 0) {
            $this->info('🎉 WebP conversion completed successfully!');
            $this->warn('Note: You may want to run "php artisan cache:clear" to clear any cached image URLs.');
        }
    }
}
