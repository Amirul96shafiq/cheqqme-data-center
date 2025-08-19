<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentMentionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_extract_mentions_from_comment_text()
    {
        // Create test users
        $user1 = User::factory()->create(['username' => 'john_doe']);
        $user2 = User::factory()->create(['username' => 'jane_smith']);
        $user3 = User::factory()->create(['username' => 'bob_wilson']);

        // Test comment with mentions
        $commentText = 'Hey @john_doe and @jane_smith, please review this task. @bob_wilson should also be aware.';

        $mentions = Comment::extractMentions($commentText);

        $this->assertCount(3, $mentions);
        $this->assertContains($user1->id, $mentions);
        $this->assertContains($user2->id, $mentions);
        $this->assertContains($user3->id, $mentions);
    }

    public function test_can_create_comment_with_mentions()
    {
        $user = User::factory()->create();
        $mentionedUser = User::factory()->create(['username' => 'mentioned_user']);

        // Create a simple task without complex relationships
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        $commentText = 'Please review this @mentioned_user';

        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => $commentText,
            'mentions' => Comment::extractMentions($commentText),
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'mentions' => json_encode([$mentionedUser->id]),
        ]);

        $this->assertCount(1, $comment->mentioned_users);
        $this->assertEquals($mentionedUser->id, $comment->mentioned_users->first()->id);
    }

    public function test_mentions_are_stored_as_json()
    {
        $user = User::factory()->create();

        // Create a simple task without complex relationships
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        $mentions = [1, 2, 3];

        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => 'Test comment with mentions',
            'mentions' => $mentions,
        ]);

        $this->assertIsArray($comment->mentions);
        $this->assertEquals($mentions, $comment->mentions);
    }

    public function test_empty_mentions_are_handled_correctly()
    {
        $user = User::factory()->create();

        // Create a simple task without complex relationships
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => 'Test comment without mentions',
            'mentions' => null,
        ]);

        $this->assertEmpty($comment->mentioned_users);
        $this->assertNull($comment->mentions);
    }

    public function test_comment_without_mentions_has_empty_mentions_array()
    {
        $user = User::factory()->create();

        // Create a simple task without complex relationships
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        $commentText = 'This is a regular comment without any mentions';

        $mentions = Comment::extractMentions($commentText);

        $this->assertEmpty($mentions);
    }

    public function test_mention_extraction_ignores_invalid_usernames()
    {
        // Create test users
        $user1 = User::factory()->create(['username' => 'valid_user']);

        // Test comment with valid and invalid mentions
        $commentText = 'Hey @valid_user and @invalid_user123 and @another_invalid!';

        $mentions = Comment::extractMentions($commentText);

        $this->assertCount(1, $mentions);
        $this->assertContains($user1->id, $mentions);
    }
}
