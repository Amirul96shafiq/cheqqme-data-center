<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kenepa\ResourceLock\Models\ResourceLock;

class CleanupExpiredResourceLocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resource-lock:cleanup {--dry-run : Show what would be deleted without actually deleting} {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired resource locks that are no longer valid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of expired resource locks...');

        // Get all resource locks
        $allLocks = ResourceLock::with('user')->get();

        $expiredLocks = $allLocks->filter(function ($lock) {
            return $lock->isExpired();
        });

        if ($expiredLocks->isEmpty()) {
            $this->info('No expired resource locks found.');

            return 0;
        }

        $this->info("Found {$expiredLocks->count()} expired resource locks:");

        // Display expired locks
        $headers = ['ID', 'User', 'Resource Type', 'Resource ID', 'Created At', 'Updated At'];
        $rows = $expiredLocks->map(function ($lock) {
            return [
                $lock->id,
                $lock->user->name ?? 'Unknown',
                $lock->lockable_type,
                $lock->lockable_id,
                $lock->created_at->format('Y-m-d H:i:s'),
                $lock->updated_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $this->table($headers, $rows);

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN: No locks were actually deleted.');

            return 0;
        }

        if ($this->option('force') || $this->confirm('Do you want to delete these expired locks?')) {
            $deletedCount = 0;

            foreach ($expiredLocks as $lock) {
                try {
                    $lock->delete();
                    $deletedCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to delete lock ID {$lock->id}: {$e->getMessage()}");
                }
            }

            $this->info("Successfully deleted {$deletedCount} expired resource locks.");
        } else {
            $this->info('Cleanup cancelled.');
        }

        return 0;
    }
}
