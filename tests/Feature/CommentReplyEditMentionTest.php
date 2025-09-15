<?php

namespace Tests\Feature;

use App\Livewire\TaskComments;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommentReplyEditMentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_reply_form_supports_mentions()
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

        $reply = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'comment' => '<p>Original reply text</p>',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TaskComments::class, ['taskId' => $task->id]);

        // Start editing the reply
        $component->call('startEditReply', $reply->id);

        // Verify editing state is set
        $this->assertEquals($reply->id, $component->get('editingReplyId'));
        $this->assertEquals('<p>Original reply text</p>', $component->get('editingReplyText'));

        // Simulate mention selection by updating the form data
        $component->set('editReplyData.editingReplyText', '<p>Hello @testuser, this is a reply!</p>');

        // Verify the form data is updated
        $this->assertEquals('<p>Hello @testuser, this is a reply!</p>', $component->get('editReplyData.editingReplyText'));

        // Save the edit
        $component->call('saveEditReply');

        // Assert reply was updated with mention
        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'comment' => '<p>Hello @testuser, this is a reply!</p>',
        ]);

        // Assert editing state is reset
        $this->assertNull($component->get('editingReplyId'));
        $this->assertEquals('', $component->get('editingReplyText'));
    }

    public function test_edit_reply_mention_extraction_works()
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

        $reply = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'comment' => '<p>Original reply text</p>',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TaskComments::class, ['taskId' => $task->id]);

        // Start editing the reply
        $component->call('startEditReply', $reply->id);

        // Update with mention
        $component->set('editReplyData.editingReplyText', '<p>Hello @testuser, this is a reply!</p>');

        // Save the edit
        $component->call('saveEditReply');

        // Assert mention was extracted and stored
        $updatedReply = $reply->fresh();
        $this->assertStringContainsString('@testuser', $updatedReply->comment);

        // Check if mentions are stored in the mentions field
        $this->assertNotNull($updatedReply->mentions);
    }

    public function test_edit_reply_form_handles_everyone_mentions()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $reply = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'comment' => '<p>Original reply text</p>',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TaskComments::class, ['taskId' => $task->id]);

        // Start editing the reply
        $component->call('startEditReply', $reply->id);

        // Update with @Everyone mention
        $component->set('editReplyData.editingReplyText', '<p>Hello @Everyone, this is a reply!</p>');

        // Save the edit
        $component->call('saveEditReply');

        // Assert @Everyone mention was processed
        $updatedReply = $reply->fresh();
        $this->assertStringContainsString('@Everyone', $updatedReply->comment);
    }
}
