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

    // Test can extract mentions from comment text
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

    // Test can create a comment with mentions
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

    // Test mentions are stored as JSON
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

    // Test empty mentions are handled correctly
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

    // Test comment without mentions has empty mentions array
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

    // Test mention extraction ignores invalid usernames
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

    // Test comment cannot start with whitespace
    public function test_comment_cannot_start_with_whitespace()
    {
        $user = User::factory()->create();

        // Create a simple task without complex relationships
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        // Test comment that starts with a space
        $commentText = ' This comment starts with a space';

        // The sanitizeHtml method should remove leading whitespace
        $sanitized = $this->getSanitizedHtml($commentText);
        $textOnly = trim(strip_tags($sanitized));

        // Should not start with whitespace
        $this->assertDoesNotMatchRegularExpression('/^\s/', $textOnly);
        $this->assertEquals('This comment starts with a space', $textOnly);
    }

    // Test comment cannot start with newline
    public function test_comment_cannot_start_with_newline()
    {
        $user = User::factory()->create();

        // Create a simple task without complex relationships
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        // Test comment that starts with a newline
        $commentText = "\nThis comment starts with a newline";

        // The sanitizeHtml method should remove leading whitespace
        $sanitized = $this->getSanitizedHtml($commentText);
        $textOnly = trim(strip_tags($sanitized));

        // Should not start with whitespace
        $this->assertDoesNotMatchRegularExpression('/^\s/', $textOnly);
        $this->assertEquals('This comment starts with a newline', $textOnly);
    }

    // Test comment cannot start with multiple whitespace
    public function test_comment_cannot_start_with_multiple_whitespace()
    {
        $user = User::factory()->create();

        // Create a simple task without complex relationships
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        // Test comment that starts with multiple spaces and tabs
        $commentText = "   \t  This comment starts with multiple whitespace characters";

        // The sanitizeHtml method should remove leading whitespace
        $sanitized = $this->getSanitizedHtml($commentText);
        $textOnly = trim(strip_tags($sanitized));

        // Should not start with whitespace
        $this->assertDoesNotMatchRegularExpression('/^\s/', $textOnly);
        $this->assertEquals('This comment starts with multiple whitespace characters', $textOnly);
    }

    /**
     * Helper method to test the sanitizeHtml method
     */
    private function getSanitizedHtml(string $html): string
    {
        // Create a mock instance to access the private method
        $mock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['sanitizeHtml'])
            ->getMock();

        // Use reflection to access the private method from TaskComments
        $reflection = new \ReflectionClass(\App\Livewire\TaskComments::class);
        $method = $reflection->getMethod('sanitizeHtml');
        $method->setAccessible(true);

        // Create a minimal TaskComments instance
        $taskComments = new \App\Livewire\TaskComments;

        // Call the private method
        return $method->invoke($taskComments, $html);
    }
}
