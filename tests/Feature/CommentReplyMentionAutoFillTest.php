<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommentReplyMentionAutoFillTest extends TestCase
{
    use RefreshDatabase;

    public function test_reply_form_can_detect_input_id_correctly()
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

        // Start reply mode
        $component->call('startReply', $parentComment->id);

        // Verify reply state is properly set
        $this->assertEquals($parentComment->id, $component->get('replyingToId'));
        $this->assertEquals('', $component->get('replyText'));

        // Test that replyData is properly initialized
        $replyData = $component->get('replyData');
        $this->assertArrayHasKey('replyText', $replyData);
        $this->assertEquals('', $replyData['replyText']);
    }

    public function test_reply_form_supports_mention_processing()
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

        $component = Livewire::test(\App\Livewire\TaskComments::class, ['taskId' => $task->id]);

        // Start reply mode
        $component->call('startReply', $parentComment->id);

        // Simulate adding a reply with mentions by updating replyData
        $component->set('replyData', ['replyText' => 'Hello @testuser, this is a reply!']);

        // Test that the reply text is properly set
        $this->assertEquals('Hello @testuser, this is a reply!', $component->get('replyText'));

        // Test that replyData is updated
        $replyData = $component->get('replyData');
        $this->assertEquals('Hello @testuser, this is a reply!', $replyData['replyText']);
    }

    public function test_reply_mention_extraction_and_processing()
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

        // Test mention extraction from reply text
        $replyText = 'Hello @testuser, this is a reply with mentions!';
        $mentions = Comment::extractMentions($replyText);

        $this->assertContains($mentionedUser->id, $mentions);

        // Test creating a reply with mentions
        $reply = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'comment' => $replyText,
            'mentions' => $mentions,
        ]);

        // Verify the reply was created correctly
        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'parent_id' => $parentComment->id,
            'comment' => $replyText,
        ]);

        // Verify mentions are properly stored
        $this->assertEquals($mentions, $reply->mentions);

        // Verify the reply is properly linked to parent
        $this->assertTrue($parentComment->replies->contains($reply));
    }

    public function test_reply_form_validation_includes_reply_text()
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

        // Test that replyText property exists and is accessible
        $this->assertTrue(property_exists($component->instance(), 'replyText'));

        // Test that replyData array exists and has the correct structure
        $replyData = $component->get('replyData');
        $this->assertIsArray($replyData);
        $this->assertArrayHasKey('replyText', $replyData);

        // Test that we can set and get replyData
        $component->set('replyData', ['replyText' => 'Test reply text']);
        $this->assertEquals('Test reply text', $component->get('replyText'));

        // Test that replyData is updated when replyData changes
        $updatedReplyData = $component->get('replyData');
        $this->assertEquals('Test reply text', $updatedReplyData['replyText']);
    }

    public function test_reply_form_handles_everyone_mentions()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        // Test @Everyone mention extraction
        $replyText = 'Hello @everyone, this is a reply for all!';
        $mentions = Comment::extractMentions($replyText);

        $this->assertContains('@Everyone', $mentions);

        // Test creating a reply with @Everyone mention
        $reply = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'comment' => $replyText,
            'mentions' => $mentions,
        ]);

        // Verify the reply was created correctly
        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'parent_id' => $parentComment->id,
            'comment' => $replyText,
        ]);

        // Verify @Everyone mention is properly stored
        $this->assertContains('@Everyone', $reply->mentions);
    }
}
