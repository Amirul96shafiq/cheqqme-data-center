<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CommentReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_reply_to_comment()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();
        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        // Test creating a reply
        $reply = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
            'comment' => 'This is a reply',
        ]);

        // Assertions
        $this->assertDatabaseHas('comments', [
            'id' => $reply->id,
            'parent_id' => $parentComment->id,
            'comment' => 'This is a reply',
        ]);

        // Test relationship
        $this->assertEquals($parentComment->id, $reply->parent_id);
        $this->assertTrue($parentComment->replies->contains($reply));
    }

    public function test_comment_model_has_reply_relationships()
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

        // Test parent relationship
        $this->assertInstanceOf(Comment::class, $reply->parent);
        $this->assertEquals($parentComment->id, $reply->parent->id);

        // Test replies relationship
        $this->assertTrue($parentComment->replies->contains($reply));
        $this->assertEquals(1, $parentComment->replies->count());
    }

    public function test_task_comments_component_can_handle_replies()
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

        // Test that only parent comments are loaded in the main list
        $component = Livewire::test(\App\Livewire\TaskComments::class, ['taskId' => $task->id]);

        $comments = $component->get('comments');
        $this->assertCount(1, $comments);
        $this->assertEquals($parentComment->id, $comments->first()->id);

        // Test that replies are loaded with the parent comment
        $this->assertTrue($comments->first()->replies->contains($reply));
    }
}
