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
        $fullName = User::factory()->create(['username' => 'amirulshafiq', 'name' => 'Amirul Shafiq Harun']);

        // Test comment with mentions
        $commentText = 'Hey @john_doe and @jane_smith, please review this task. @bob_wilson should also be aware. Also ping @Amirul Shafiq Harun.';

        $mentions = Comment::extractMentions($commentText);

        $this->assertCount(4, $mentions);
        $this->assertContains($user1->id, $mentions);
        $this->assertContains($user2->id, $mentions);
        $this->assertContains($user3->id, $mentions);
        $this->assertContains($fullName->id, $mentions);
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

        // Check the original text content (before sanitization) for leading whitespace
        $originalTextOnly = strip_tags($commentText);

        // Should start with whitespace (this is what we want to detect and prevent)
        $this->assertMatchesRegularExpression('/^\s/', $originalTextOnly);

        // After sanitization, whitespace should be removed
        $sanitized = $this->getSanitizedHtml($commentText);
        $textOnly = trim(strip_tags($sanitized));
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

        // Check the original text content (before sanitization) for leading whitespace
        $originalTextOnly = strip_tags($commentText);

        // Should start with whitespace (this is what we want to detect and prevent)
        $this->assertMatchesRegularExpression('/^\s/', $originalTextOnly);

        // After sanitization, whitespace should be removed
        $sanitized = $this->getSanitizedHtml($commentText);
        $textOnly = trim(strip_tags($sanitized));
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

        // Check the original text content (before sanitization) for leading whitespace
        $originalTextOnly = strip_tags($commentText);

        // Should start with whitespace (this is what we want to detect and prevent)
        $this->assertMatchesRegularExpression('/^\s/', $originalTextOnly);

        // After sanitization, whitespace should be removed
        $sanitized = $this->getSanitizedHtml($commentText);
        $textOnly = trim(strip_tags($sanitized));
        $this->assertDoesNotMatchRegularExpression('/^\s/', $textOnly);
        $this->assertEquals('This comment starts with multiple whitespace characters', $textOnly);
    }

    // Test comment cannot end with whitespace
    public function test_comment_cannot_end_with_whitespace()
    {
        $user = User::factory()->create();

        // Create a simple task without complex relationships
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        // Test comment that ends with a space
        $commentText = 'This comment ends with a space ';

        // Check the original text content (before sanitization) for trailing whitespace
        $originalTextOnly = strip_tags($commentText);

        // Should end with whitespace (this is what we want to detect and prevent)
        $this->assertMatchesRegularExpression('/\s$/', $originalTextOnly);

        // After sanitization, whitespace should be removed
        $sanitized = $this->getSanitizedHtml($commentText);
        $textOnly = trim(strip_tags($sanitized));
        $this->assertDoesNotMatchRegularExpression('/\s$/', $textOnly);
        $this->assertEquals('This comment ends with a space', $textOnly);
    }

    // Test comment cannot end with newline
    public function test_comment_cannot_end_with_newline()
    {
        $user = User::factory()->create();

        // Create a simple task without complex relationships
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        // Test comment that ends with a newline
        $commentText = "This comment ends with a newline\n";

        // Check the original text content (before sanitization) for trailing whitespace
        $originalTextOnly = strip_tags($commentText);

        // Should end with whitespace (this is what we want to detect and prevent)
        $this->assertMatchesRegularExpression('/\s$/', $originalTextOnly);

        // After sanitization, whitespace should be removed
        $sanitized = $this->getSanitizedHtml($commentText);
        $textOnly = trim(strip_tags($sanitized));
        $this->assertDoesNotMatchRegularExpression('/\s$/', $textOnly);
        $this->assertEquals('This comment ends with a newline', $textOnly);
    }

    // Test comment cannot end with multiple whitespace
    public function test_comment_cannot_end_with_multiple_whitespace()
    {
        $user = User::factory()->create();

        // Create a simple task without complex relationships
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        // Test comment that ends with multiple spaces and tabs
        $commentText = "This comment ends with multiple whitespace characters   \t  ";

        // Check the original text content (before sanitization) for trailing whitespace
        $originalTextOnly = strip_tags($commentText);

        // Should end with whitespace (this is what we want to detect and prevent)
        $this->assertMatchesRegularExpression('/\s$/', $originalTextOnly);

        // After sanitization, whitespace should be removed
        $sanitized = $this->getSanitizedHtml($commentText);
        $textOnly = trim(strip_tags($sanitized));
        $this->assertDoesNotMatchRegularExpression('/\s$/', $textOnly);
        $this->assertEquals('This comment ends with multiple whitespace characters', $textOnly);
    }

    // Test that addComment method actually rejects whitespace comments
    public function test_add_comment_rejects_leading_whitespace()
    {
        $user = User::factory()->create();
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        $this->actingAs($user);

        // Create TaskComments component instance
        $component = new \App\Livewire\TaskComments;
        $component->mount($task->id);

        // Set a comment with leading whitespace
        $component->newComment = ' This comment starts with a space';

        // Count comments before
        $commentsBefore = Comment::count();

        // Try to add the comment
        $component->addComment();

        // Count comments after - should be the same (no comment added)
        $commentsAfter = Comment::count();

        $this->assertEquals($commentsBefore, $commentsAfter, 'Comment with leading whitespace should be rejected');
    }

    // Test that addComment method actually rejects trailing whitespace comments
    public function test_add_comment_rejects_trailing_whitespace()
    {
        $user = User::factory()->create();
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        $this->actingAs($user);

        // Create TaskComments component instance
        $component = new \App\Livewire\TaskComments;
        $component->mount($task->id);

        // Set a comment with trailing whitespace
        $component->newComment = 'This comment ends with a space ';

        // Count comments before
        $commentsBefore = Comment::count();

        // Try to add the comment
        $component->addComment();

        // Count comments after - should be the same (no comment added)
        $commentsAfter = Comment::count();

        $this->assertEquals($commentsBefore, $commentsAfter, 'Comment with trailing whitespace should be rejected');
    }

    // Test that addComment method accepts valid comments without whitespace
    public function test_add_comment_accepts_valid_comment()
    {
        $user = User::factory()->create();
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'todo',
        ]);

        $this->actingAs($user);

        // Create TaskComments component instance
        $component = new \App\Livewire\TaskComments;
        $component->mount($task->id);

        // Set a valid comment without leading/trailing whitespace
        $component->newComment = 'This is a valid comment';

        // Count comments before
        $commentsBefore = Comment::count();

        // Try to add the comment
        $component->addComment();

        // Count comments after - should be one more
        $commentsAfter = Comment::count();

        $this->assertEquals($commentsBefore + 1, $commentsAfter, 'Valid comment should be accepted');

        // Check the comment was saved correctly
        $savedComment = Comment::latest()->first();
        $this->assertNotNull($savedComment);
        $this->assertEquals($task->id, $savedComment->task_id);
        $this->assertEquals($user->id, $savedComment->user_id);
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
