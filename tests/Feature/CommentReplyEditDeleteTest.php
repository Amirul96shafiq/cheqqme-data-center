<?php

namespace Tests\Feature;

use App\Livewire\TaskComments;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommentReplyEditDeleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_edit_reply_comment()
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

        // Verify editing state is set
        $this->assertEquals($reply->id, $component->get('editingReplyId'));
        $this->assertEquals('<p>Original reply text</p>', $component->get('editingReplyText'));

        // Update the reply text
        $component->set('editingReplyText', '<p>Updated reply text</p>');
        $component->set('editReplyData.editingReplyText', '<p>Updated reply text</p>');

        // Save the edit
        $component->call('saveEditReply');

        // Assert reply was updated
        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'comment' => '<p>Updated reply text</p>',
        ]);

        // Assert editing state is reset
        $this->assertNull($component->get('editingReplyId'));
        $this->assertEquals('', $component->get('editingReplyText'));
    }

    public function test_can_cancel_edit_reply()
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

        // Verify editing state is set
        $this->assertEquals($reply->id, $component->get('editingReplyId'));

        // Cancel editing
        $component->call('cancelEditReply');

        // Assert editing state is reset
        $this->assertNull($component->get('editingReplyId'));
        $this->assertEquals('', $component->get('editingReplyText'));

        // Assert reply was not changed
        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'comment' => '<p>Original reply text</p>',
        ]);
    }

    public function test_can_delete_reply_comment()
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
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TaskComments::class, ['taskId' => $task->id]);

        // Confirm delete
        $component->call('confirmDeleteReply', $reply->id);

        // Verify delete confirmation state is set
        $this->assertEquals($reply->id, $component->get('confirmingDeleteReplyId'));

        // Perform delete
        $component->call('deleteReply');

        // Assert reply was deleted (soft deleted)
        $this->assertSoftDeleted('comments', [
            'id' => $reply->id,
        ]);

        // Assert delete confirmation state is reset
        $this->assertNull($component->get('confirmingDeleteReplyId'));
    }

    public function test_can_cancel_delete_reply()
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
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TaskComments::class, ['taskId' => $task->id]);

        // Confirm delete
        $component->call('confirmDeleteReply', $reply->id);

        // Verify delete confirmation state is set
        $this->assertEquals($reply->id, $component->get('confirmingDeleteReplyId'));

        // Cancel delete
        $component->call('cancelDeleteReply');

        // Assert delete confirmation state is reset
        $this->assertNull($component->get('confirmingDeleteReplyId'));

        // Assert reply was not deleted
        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'deleted_at' => null,
        ]);
    }

    public function test_cannot_edit_reply_by_other_user()
    {
        // Create test data
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $reply = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $otherUser->id, // Different user
            'parent_id' => $parentComment->id,
            'comment' => '<p>Original reply text</p>',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TaskComments::class, ['taskId' => $task->id]);

        // Try to start editing the reply (should fail)
        $component->call('startEditReply', $reply->id);

        // Verify editing state is not set
        $this->assertNull($component->get('editingReplyId'));

        // Assert reply was not changed
        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'comment' => '<p>Original reply text</p>',
        ]);
    }

    public function test_cannot_delete_reply_by_other_user()
    {
        // Create test data
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $reply = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $otherUser->id, // Different user
            'parent_id' => $parentComment->id,
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TaskComments::class, ['taskId' => $task->id]);

        // Try to confirm delete (should fail)
        $component->call('confirmDeleteReply', $reply->id);

        // Verify delete confirmation state is not set
        $this->assertNull($component->get('confirmingDeleteReplyId'));

        // Assert reply was not deleted
        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'deleted_at' => null,
        ]);
    }

    public function test_reply_edit_form_validation()
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
        ]);

        $this->actingAs($user);

        $component = Livewire::test(TaskComments::class, ['taskId' => $task->id]);

        // Start editing the reply
        $component->call('startEditReply', $reply->id);

        // Try to save with empty text (should fail validation)
        $component->set('editingReplyText', '');
        $component->set('editReplyData.editingReplyText', '');
        $component->call('saveEditReply');

        // Assert validation error
        $component->assertHasErrors(['editingReplyText']);

        // Assert reply was not changed
        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'comment' => $reply->comment,
        ]);
    }
}
