<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TemporaryFileService
{
    /**
     * Store a file temporarily and return its temporary ID.
     */
    public function storeTemporarily(UploadedFile $file): array
    {
        // Generate a unique temporary ID
        $tempId = Str::uuid()->toString();

        // Create a temporary directory structure based on session/user
        $tempPath = 'temp-uploads/'.session()->getId().'/'.$tempId;

        // Store the file with its original name
        $storedPath = $file->storeAs($tempPath, $file->getClientOriginalName(), 'public');

        return [
            'temp_id' => $tempId,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $storedPath,
            'uploaded_at' => now(),
        ];
    }

    /**
     * Get all temporary files for the current session.
     */
    public function getSessionFiles(): array
    {
        $sessionId = session()->getId();
        $tempDir = 'temp-uploads/'.$sessionId;

        if (! Storage::disk('public')->exists($tempDir)) {
            return [];
        }

        $files = [];
        $directories = Storage::disk('public')->directories($tempDir);

        foreach ($directories as $dir) {
            $tempId = basename($dir);
            $fileList = Storage::disk('public')->files($dir);

            foreach ($fileList as $file) {
                $files[] = [
                    'temp_id' => $tempId,
                    'original_name' => basename($file),
                    'path' => $file,
                    'mime_type' => Storage::disk('public')->mimeType($file),
                    'size' => Storage::disk('public')->size($file),
                    'uploaded_at' => now(), // We don't store this, so use current time
                ];
            }
        }

        return $files;
    }

    /**
     * Move temporary files to permanent storage and return their paths.
     */
    public function moveToPermanent(array $tempIds): array
    {
        $permanentPaths = [];

        foreach ($tempIds as $tempId) {
            $tempDir = 'temp-uploads/'.session()->getId().'/'.$tempId;

            if (! Storage::disk('public')->exists($tempDir)) {
                continue;
            }

            $files = Storage::disk('public')->files($tempDir);

            foreach ($files as $file) {
                // Move to permanent tasks directory
                $fileName = basename($file);
                $permanentPath = 'tasks/'.$fileName;

                // If file already exists, add timestamp to avoid conflicts
                if (Storage::disk('public')->exists($permanentPath)) {
                    $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                    $baseName = pathinfo($fileName, PATHINFO_FILENAME);
                    $permanentPath = 'tasks/'.$baseName.'_'.time().'.'.$extension;
                }

                Storage::disk('public')->move($file, $permanentPath);
                $permanentPaths[] = $permanentPath;
            }

            // Remove the temporary directory
            Storage::disk('public')->deleteDirectory($tempDir);
        }

        return $permanentPaths;
    }

    /**
     * Delete temporary files by their temp IDs.
     */
    public function deleteTemporary(array $tempIds): void
    {
        $sessionId = session()->getId();

        foreach ($tempIds as $tempId) {
            $tempDir = 'temp-uploads/'.$sessionId.'/'.$tempId;
            Storage::disk('public')->deleteDirectory($tempDir);
        }
    }

    /**
     * Clean up old temporary files (older than specified minutes).
     */
    public function cleanupOldFiles(int $minutesOld = 60): int
    {
        $tempBaseDir = 'temp-uploads';
        $cutoffTime = now()->subMinutes($minutesOld);
        $deletedCount = 0;

        if (! Storage::disk('public')->exists($tempBaseDir)) {
            return 0;
        }

        $sessionDirs = Storage::disk('public')->directories($tempBaseDir);

        foreach ($sessionDirs as $sessionDir) {
            $tempIdDirs = Storage::disk('public')->directories($sessionDir);

            foreach ($tempIdDirs as $tempIdDir) {
                $files = Storage::disk('public')->files($tempIdDir);

                foreach ($files as $file) {
                    $lastModified = Storage::disk('public')->lastModified($file);

                    if ($lastModified < $cutoffTime->timestamp) {
                        Storage::disk('public')->delete($file);
                        $deletedCount++;
                    }
                }

                // Remove empty directories
                if (empty(Storage::disk('public')->files($tempIdDir))) {
                    Storage::disk('public')->deleteDirectory($tempIdDir);
                }
            }

            // Remove empty session directories
            if (empty(Storage::disk('public')->allDirectories($sessionDir))) {
                Storage::disk('public')->deleteDirectory($sessionDir);
            }
        }

        return $deletedCount;
    }

    /**
     * Get file size formatted for display.
     */
    public static function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 Bytes';
        }
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i) * 100) / 100 .' '.$sizes[$i];
    }
}
