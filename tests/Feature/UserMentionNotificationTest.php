<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserMentionNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mentions_by_full_name_create_notifications_for_self_and_other(): void
    {
        $author = User::factory()->create([
            'username' => 'amirulshafiq',
            'name' => 'Amirul Shafiq Harun',
        ]);

        $other = User::factory()->create([
            'username' => 'amirul_other',
            'name' => 'Amirul Other Account',
        ]);

        $task = Task::create([
            'title' => 'Notify Mentions',
            'description' => 'Ensure mentions send notifications',
            'status' => 'todo',
        ]);

        $this->actingAs($author);

        $text = "test @{$author->name} self comment and @{$other->name} for other mentioned";

        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $author->id,
            'comment' => $text,
            'mentions' => Comment::extractMentions($text),
        ]);

        $comment->processMentions();

        // Assert notifications exist for both the author (self-mention) and the other user
        $this->assertDatabaseHas('notifications', [
            'type' => 'user_mentioned',
            'notifiable_type' => User::class,
            'notifiable_id' => $author->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => 'user_mentioned',
            'notifiable_type' => User::class,
            'notifiable_id' => $other->id,
        ]);
    }
}
