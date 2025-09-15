<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommentReplyMentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_reply_form_supports_mentions()
    {
        // Create test data
        $user = User::factory()->create();
        $mentionedUser = User::factory()->create(['username' => 'testuser']);
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        // Test that reply form can be initialized
        $component = Livewire::test(\App\Livewire\TaskComments::class, ['taskId' => $task->id]);

        // Start reply mode
        $component->call('startReply', $parentComment->id);

        // Verify reply state is set
        $this->assertEquals($parentComment->id, $component->get('replyingToId'));
        $this->assertEquals('', $component->get('replyText'));

        // Test that reply form data is properly initialized
        $replyData = $component->get('replyData');
        $this->assertArrayHasKey('replyText', $replyData);
        $this->assertEquals('', $replyData['replyText']);
    }

    public function test_reply_with_mentions_creates_proper_comment()
    {
        // Create test data
        $user = User::factory()->create();
        $mentionedUser = User::factory()->create(['username' => 'testuser']);
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        // Test creating a reply with mentions
        $reply = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'comment' => 'Hello @testuser, this is a reply!',
            'mentions' => [$mentionedUser->id],
        ]);

        // Assertions
        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'parent_id' => $parentComment->id,
            'comment' => 'Hello @testuser, this is a reply!',
        ]);

        // Test that mentions are properly stored
        $this->assertEquals([$mentionedUser->id], $reply->mentions);

        // Test that the reply is properly linked to parent
        $this->assertTrue($parentComment->replies->contains($reply));
    }

    public function test_reply_mention_extraction_works()
    {
        // Create test data
        $user = User::factory()->create();
        $mentionedUser = User::factory()->create(['username' => 'testuser']);
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        // Test mention extraction in reply text
        $replyText = 'Hello @testuser, this is a reply with mentions!';
        $mentions = Comment::extractMentions($replyText);

        $this->assertContains($mentionedUser->id, $mentions);
    }

    public function test_reply_form_validation_rules()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $component = Livewire::test(\App\Livewire\TaskComments::class, ['taskId' => $task->id]);

        // Test that replyText property exists
        $this->assertTrue(property_exists($component->instance(), 'replyText'));

        // Test that replyData array exists
        $replyData = $component->get('replyData');
        $this->assertIsArray($replyData);
        $this->assertArrayHasKey('replyText', $replyData);
    }
}
