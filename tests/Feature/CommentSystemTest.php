<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommentSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
        ]);

        // Create a test task
        $this->task = Task::factory()->create([
            'title' => 'Test Task',
            'assigned_to' => $this->user->id,
        ]);
    }

    public function test_user_can_add_comment_to_task()
    {
        $this->actingAs($this->user);

        Livewire::test('task-comments', ['taskId' => $this->task->id])
            ->set('newComment', 'This is a test comment')
            ->call('addComment')
            ->assertSet('newComment', '')
            ->assertDispatched('$refresh');

        $this->assertDatabaseHas('comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'comment' => 'This is a test comment',
        ]);
    }

    public function test_user_can_edit_own_comment()
    {
        $this->actingAs($this->user);

        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'comment' => 'Original comment',
        ]);

        Livewire::test('task-comments', ['taskId' => $this->task->id])
            ->call('startEdit', $comment->id)
            ->assertSet('editingId', $comment->id)
            ->assertSet('editingText', 'Original comment')
            ->set('editingText', 'Updated comment')
            ->call('saveEdit')
            ->assertSet('editingId', null)
            ->assertSet('editingText', '');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'comment' => 'Updated comment',
        ]);
    }

    public function test_user_can_delete_own_comment()
    {
        $this->actingAs($this->user);

        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'comment' => 'Comment to delete',
        ]);

        Livewire::test('task-comments', ['taskId' => $this->task->id])
            ->call('confirmDelete', $comment->id)
            ->assertSet('confirmingDeleteId', $comment->id)
            ->call('performDelete')
            ->assertSet('confirmingDeleteId', null);

        // Check that the comment is soft deleted
        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_user_cannot_edit_others_comment()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $otherUser->id,
            'comment' => 'Other user comment',
        ]);

        Livewire::test('task-comments', ['taskId' => $this->task->id])
            ->call('startEdit', $comment->id)
            ->assertSet('editingId', null);
    }

    public function test_user_cannot_delete_others_comment()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($this->user);

        $comment = Comment::create([
            'task_id' => $this->task->id,
            'user_id' => $otherUser->id,
            'comment' => 'Other user comment',
        ]);

        Livewire::test('task-comments', ['taskId' => $this->task->id])
            ->call('confirmDelete', $comment->id)
            ->assertSet('confirmingDeleteId', null);
    }

    public function test_comment_mentions_are_extracted_correctly()
    {
        $this->actingAs($this->user);

        $mentionedUser = User::factory()->create([
            'username' => 'mentioneduser',
            'name' => 'Mentioned User',
        ]);

        Livewire::test('task-comments', ['taskId' => $this->task->id])
            ->set('newComment', 'Hello @mentioneduser, please check this task')
            ->call('addComment');

        $this->assertDatabaseHas('comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'comment' => 'Hello @mentioneduser, please check this task',
        ]);

        $comment = Comment::where('task_id', $this->task->id)->first();
        $this->assertContains($mentionedUser->id, $comment->mentions);
    }

    public function test_comment_validation_works()
    {
        $this->actingAs($this->user);

        Livewire::test('task-comments', ['taskId' => $this->task->id])
            ->set('newComment', '')
            ->call('addComment')
            ->assertHasErrors(['newComment' => 'required']);

        Livewire::test('task-comments', ['taskId' => $this->task->id])
            ->set('newComment', str_repeat('a', 2001))
            ->call('addComment')
            ->assertHasErrors(['newComment' => 'max']);
    }
}
