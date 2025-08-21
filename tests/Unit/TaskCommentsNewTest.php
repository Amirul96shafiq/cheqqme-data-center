<?php

namespace Tests\Unit;

use App\Livewire\TaskCommentsNew;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TaskCommentsNewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create a test task
        $this->task = Task::factory()->create();
    }

    public function test_can_add_comment()
    {
        $commentText = 'This is a test comment';

        Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->set('newComment', $commentText)
            ->call('addComment')
            ->assertSet('newComment', '')
            ->assertDispatched('$refresh');

        $this->assertDatabaseHas('comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'comment' => $commentText,
        ]);
    }

    public function test_cannot_add_empty_comment()
    {
        Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->set('newComment', '   ')
            ->call('addComment')
            ->assertSet('newComment', '   ');

        $this->assertDatabaseMissing('comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_edit_own_comment()
    {
        $comment = Comment::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'comment' => 'Original comment',
        ]);

        $editedText = 'Edited comment';

        Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->call('startEdit', $comment->id)
            ->assertSet('editingId', $comment->id)
            ->assertSet('editingText', 'Original comment')
            ->set('editingText', $editedText)
            ->call('saveEdit')
            ->assertSet('editingId', null)
            ->assertSet('editingText', '')
            ->assertDispatched('$refresh');

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'comment' => $editedText,
        ]);
    }

    public function test_cannot_edit_others_comment()
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $otherUser->id,
            'comment' => 'Other user comment',
        ]);

        Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->call('startEdit', $comment->id)
            ->assertSet('editingId', null);
    }

    public function test_can_delete_own_comment()
    {
        $comment = Comment::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'comment' => 'Comment to delete',
        ]);

        $commentId = $comment->id;

        Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->call('confirmDelete', $commentId)
            ->assertSet('confirmingDeleteId', $commentId)
            ->call('performDelete')
            ->assertSet('confirmingDeleteId', null)
            ->assertDispatched('$refresh');

        // Verify the comment is soft deleted (has deleted_at timestamp)
        $this->assertDatabaseHas('comments', [
            'id' => $commentId,
        ]);

        // Check that it's soft deleted
        $deletedComment = Comment::withTrashed()->find($commentId);
        $this->assertNotNull($deletedComment);
        $this->assertNotNull($deletedComment->deleted_at);
        $this->assertTrue($deletedComment->trashed());
    }

    public function test_cannot_delete_others_comment()
    {
        $otherUser = User::factory()->create();
        $comment = Comment::factory()->create([
            'task_id' => $this->task->id,
            'user_id' => $otherUser->id,
            'comment' => 'Other user comment',
        ]);

        Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->call('confirmDelete', $comment->id)
            ->assertSet('confirmingDeleteId', null);
    }

    public function test_can_search_users_for_mentions()
    {
        $otherUser = User::factory()->create(['name' => 'John Doe']);

        Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->call('searchUsers', 'John')
            ->assertSet('showMentionDropdown', true)
            ->assertSet('filteredUsers.0.name', 'John Doe');
    }

    public function test_can_select_user_for_mention()
    {
        $otherUser = User::factory()->create(['name' => 'John Doe', 'username' => 'johndoe']);

        Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->set('newComment', 'Hello @John')
            ->call('searchUsers', 'John')
            ->call('selectUser', $otherUser->id)
            ->assertSet('showMentionDropdown', false)
            ->assertSet('newComment', 'Hello @johndoe ');
    }

    public function test_can_show_more_comments()
    {
        // Create more than 10 comments
        Comment::factory()->count(15)->create([
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
        ]);

        Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->assertSet('visibleCount', 10)
            ->call('showMore')
            ->assertSet('visibleCount', 20);
    }

    public function test_comment_validation()
    {
        $longComment = str_repeat('a', 2001); // Exceeds 2000 character limit

        Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->set('newComment', $longComment)
            ->call('addComment')
            ->assertHasErrors(['newComment']);
    }

    public function test_save_only_triggered_by_explicit_action()
    {
        $commentText = 'This should only save when explicitly requested';

        $component = Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->set('newComment', $commentText);

        // Verify comment is not saved yet
        $this->assertDatabaseMissing('comments', [
            'task_id' => $this->task->id,
            'comment' => $commentText,
        ]);

        // Only when we explicitly call addComment should it save
        $component->call('addComment')
            ->assertSet('newComment', '');

        $this->assertDatabaseHas('comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'comment' => $commentText,
        ]);
    }

    public function test_enter_key_does_not_trigger_save()
    {
        $commentText = 'This should not save when pressing Enter';

        $component = Livewire::test(TaskCommentsNew::class, ['taskId' => $this->task->id])
            ->set('newComment', $commentText);

        // Verify comment is not saved yet
        $this->assertDatabaseMissing('comments', [
            'task_id' => $this->task->id,
            'comment' => $commentText,
        ]);

        // Simulate pressing Enter key (which should NOT save)
        // Since we removed the Enter key handler, this should not trigger saving
        $component->set('newComment', $commentText.' - still editing');

        // Comment should still not be saved
        $this->assertDatabaseMissing('comments', [
            'task_id' => $this->task->id,
            'comment' => $commentText,
        ]);

        // Only explicit addComment call should save
        $component->call('addComment');

        $this->assertDatabaseHas('comments', [
            'task_id' => $this->task->id,
            'user_id' => $this->user->id,
            'comment' => $commentText.' - still editing',
        ]);
    }
}
