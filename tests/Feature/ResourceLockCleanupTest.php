<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Kenepa\ResourceLock\Models\ResourceLock;
use Tests\TestCase;

class ResourceLockCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_command_removes_expired_locks(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create a task
        $task = Task::factory()->create();

        // Create an expired lock (older than timeout)
        $expiredLock = new ResourceLock;
        $expiredLock->user_id = $user->id;
        $expiredLock->lockable_type = Task::class;
        $expiredLock->lockable_id = $task->id;
        $expiredLock->created_at = Carbon::now()->subHours(2); // 2 hours ago
        $expiredLock->updated_at = Carbon::now()->subHours(2); // 2 hours ago
        $expiredLock->save();

        // Create a current lock
        $currentLock = new ResourceLock;
        $currentLock->user_id = $user->id;
        $currentLock->lockable_type = Task::class;
        $currentLock->lockable_id = $task->id;
        $currentLock->created_at = Carbon::now()->subMinutes(5); // 5 minutes ago
        $currentLock->updated_at = Carbon::now()->subMinutes(5); // 5 minutes ago
        $currentLock->save();

        // Verify both locks exist
        $this->assertDatabaseHas('resource_locks', ['id' => $expiredLock->id]);
        $this->assertDatabaseHas('resource_locks', ['id' => $currentLock->id]);

        // Run the cleanup command
        Artisan::call('resource-lock:cleanup', ['--force' => true]);

        // Verify expired lock is removed but current lock remains
        $this->assertDatabaseMissing('resource_locks', ['id' => $expiredLock->id]);
        $this->assertDatabaseHas('resource_locks', ['id' => $currentLock->id]);
    }

    public function test_cleanup_command_with_dry_run_does_not_delete(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create a task
        $task = Task::factory()->create();

        // Create an expired lock
        $expiredLock = new ResourceLock;
        $expiredLock->user_id = $user->id;
        $expiredLock->lockable_type = Task::class;
        $expiredLock->lockable_id = $task->id;
        $expiredLock->created_at = Carbon::now()->subHours(2);
        $expiredLock->updated_at = Carbon::now()->subHours(2);
        $expiredLock->save();

        // Run the cleanup command with dry-run
        $output = Artisan::call('resource-lock:cleanup', ['--dry-run' => true]);

        // Verify lock still exists
        $this->assertDatabaseHas('resource_locks', ['id' => $expiredLock->id]);
        $this->assertEquals(0, $output);
    }

    public function test_cleanup_command_with_no_expired_locks(): void
    {
        // Create a user
        $user = User::factory()->create();

        // Create a task
        $task = Task::factory()->create();

        // Create a current lock (not expired)
        $currentLock = new ResourceLock;
        $currentLock->user_id = $user->id;
        $currentLock->lockable_type = Task::class;
        $currentLock->lockable_id = $task->id;
        $currentLock->created_at = Carbon::now()->subMinutes(5);
        $currentLock->updated_at = Carbon::now()->subMinutes(5);
        $currentLock->save();

        // Run the cleanup command
        $output = Artisan::call('resource-lock:cleanup', ['--force' => true]);

        // Verify lock still exists
        $this->assertDatabaseHas('resource_locks', ['id' => $currentLock->id]);
        $this->assertEquals(0, $output);
    }
}
