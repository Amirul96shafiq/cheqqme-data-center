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
            'type' => 'Filament\\Notifications\\DatabaseNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $author->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'type' => 'Filament\\Notifications\\DatabaseNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $other->id,
        ]);
    }

    public function test_mention_notification_contains_comment_action_url(): void
    {
        $author = User::factory()->create([
            'username' => 'author',
            'name' => 'Author User',
        ]);

        $mentioned = User::factory()->create([
            'username' => 'mentioned',
            'name' => 'Mentioned User',
        ]);

        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Task for testing notification actions',
            'status' => 'todo',
        ]);

        $this->actingAs($author);

        $text = "Hello @{$mentioned->name}, please check this out!";

        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $author->id,
            'comment' => $text,
            'mentions' => Comment::extractMentions($text),
        ]);

        $comment->processMentions();

        // Get the notification for the mentioned user
        $notification = $mentioned->notifications()->first();

        $this->assertNotNull($notification, 'Notification should exist for mentioned user');

        // Verify notification data contains the expected structure
        $data = $notification->data;
        $this->assertIsArray($data);
        $this->assertArrayHasKey('actions', $data);
        $this->assertIsArray($data['actions']);
        $this->assertNotEmpty($data['actions']);

        // Verify the first action has a URL that includes the comment ID
        $firstAction = $data['actions'][0] ?? null;
        $this->assertNotNull($firstAction);
        $this->assertArrayHasKey('url', $firstAction);
        $this->assertStringContainsString("/comments/{$comment->id}", $firstAction['url']);
    }

    public function test_mention_notification_for_reply_comment_links_to_parent_comment(): void
    {
        $author = User::factory()->create([
            'username' => 'author',
            'name' => 'Author User',
        ]);

        $mentioned = User::factory()->create([
            'username' => 'mentioned',
            'name' => 'Mentioned User',
        ]);

        $task = Task::create([
            'title' => 'Test Task with Reply',
            'description' => 'Task for testing reply comment notifications',
            'status' => 'todo',
        ]);

        $this->actingAs($author);

        // Create parent comment
        $parentComment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $author->id,
            'comment' => 'This is the parent comment',
            'mentions' => [],
        ]);

        // Create reply comment with mention
        $replyText = "Hello @{$mentioned->name}, this is a reply!";
        $replyComment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $author->id,
            'parent_id' => $parentComment->id,
            'comment' => $replyText,
            'mentions' => Comment::extractMentions($replyText),
        ]);

        $replyComment->processMentions();

        // Get the notification for the mentioned user
        $notification = $mentioned->notifications()->first();

        $this->assertNotNull($notification, 'Notification should exist for mentioned user');

        // Verify the action URL points to the parent comment, not the reply
        $data = $notification->data;
        $firstAction = $data['actions'][0] ?? null;
        $this->assertNotNull($firstAction);
        $this->assertArrayHasKey('url', $firstAction);
        $this->assertStringContainsString("/comments/{$parentComment->id}", $firstAction['url']);
        $this->assertStringNotContainsString("/comments/{$replyComment->id}", $firstAction['url']);
    }
}
