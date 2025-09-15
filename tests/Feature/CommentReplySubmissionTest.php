<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommentReplySubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_reply_submission_works_with_valid_text()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(\App\Livewire\TaskComments::class, ['taskId' => $task->id]);

        // Start reply mode
        $component->call('startReply', $parentComment->id);

        // Verify reply state is set
        $this->assertEquals($parentComment->id, $component->get('replyingToId'));

        // Set reply text directly
        $component->set('replyText', 'This is a test reply');

        // Verify reply text is set
        $this->assertEquals('This is a test reply', $component->get('replyText'));

        // Try to submit the reply
        $component->call('addReply');

        // Check if reply was created
        $this->assertDatabaseHas('comments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'comment' => 'This is a test reply',
        ]);

        // Verify reply state is cleared
        $this->assertNull($component->get('replyingToId'));
        $this->assertEquals('', $component->get('replyText'));
    }

    public function test_reply_submission_fails_with_empty_text()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(\App\Livewire\TaskComments::class, ['taskId' => $task->id]);

        // Start reply mode
        $component->call('startReply', $parentComment->id);

        // Leave reply text empty
        $component->set('replyText', '');

        // Try to submit the reply
        $component->call('addReply');

        // Check that no reply was created
        $this->assertDatabaseMissing('comments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
        ]);

        // Verify reply state is still set (form should remain open)
        $this->assertEquals($parentComment->id, $component->get('replyingToId'));
    }

    public function test_reply_submission_works_with_reply_data()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(\App\Livewire\TaskComments::class, ['taskId' => $task->id]);

        // Start reply mode
        $component->call('startReply', $parentComment->id);

        // Set reply text via replyData
        $component->set('replyData', ['replyText' => 'This is a test reply via replyData']);

        // Verify reply text is set
        $this->assertEquals('This is a test reply via replyData', $component->get('replyText'));

        // Try to submit the reply
        $component->call('addReply');

        // Check if reply was created
        $this->assertDatabaseHas('comments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'comment' => 'This is a test reply via replyData',
        ]);
    }

    public function test_reply_submission_validation_rules()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(\App\Livewire\TaskComments::class, ['taskId' => $task->id]);

        // Start reply mode
        $component->call('startReply', $parentComment->id);

        // Test with text that's too long
        $longText = str_repeat('a', 1001);
        $component->set('replyText', $longText);

        // Try to submit the reply
        $component->call('addReply');

        // Should have validation errors
        $component->assertHasErrors(['replyText']);

        // Check that no reply was created
        $this->assertDatabaseMissing('comments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
        ]);
    }
}
