<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentReplyEmojiReactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_emoji_reaction_can_be_added_to_reply_comment()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $replyComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
        ]);

        $this->actingAs($user);

        // Test adding emoji reaction to reply comment
        $response = $this->postJson('/api/comment-reactions', [
            'comment_id' => $replyComment->id,
            'emoji' => '👍',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'action' => 'added',
                'emoji' => '👍',
                'reaction_count' => 1,
            ],
        ]);

        // Verify reaction was stored in database
        $this->assertDatabaseHas('comment_reactions', [
            'comment_id' => $replyComment->id,
            'user_id' => $user->id,
            'emoji' => '👍',
        ]);

        // Verify reaction count is correct
        $this->assertEquals(1, $replyComment->reactions()->where('emoji', '👍')->count());
    }

    public function test_emoji_reaction_can_be_removed_from_reply_comment()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $replyComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
        ]);

        // Create existing reaction
        CommentReaction::create([
            'comment_id' => $replyComment->id,
            'user_id' => $user->id,
            'emoji' => '👍',
        ]);

        $this->actingAs($user);

        // Test removing emoji reaction from reply comment
        $response = $this->postJson('/api/comment-reactions', [
            'comment_id' => $replyComment->id,
            'emoji' => '👍',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'action' => 'removed',
                'emoji' => '👍',
                'reaction_count' => 0,
            ],
        ]);

        // Verify reaction was removed from database
        $this->assertDatabaseMissing('comment_reactions', [
            'comment_id' => $replyComment->id,
            'user_id' => $user->id,
            'emoji' => '👍',
        ]);

        // Verify reaction count is correct
        $this->assertEquals(0, $replyComment->reactions()->where('emoji', '👍')->count());
    }

    public function test_can_get_emoji_reactions_for_reply_comment()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $replyComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
        ]);

        // Create reactions
        CommentReaction::create([
            'comment_id' => $replyComment->id,
            'user_id' => $user->id,
            'emoji' => '👍',
        ]);

        CommentReaction::create([
            'comment_id' => $replyComment->id,
            'user_id' => $user->id,
            'emoji' => '❤️',
        ]);

        $this->actingAs($user);

        // Test getting emoji reactions for reply comment
        $response = $this->getJson("/api/comments/{$replyComment->id}/reactions");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        // Check that both emojis are present
        $emojis = collect($data)->pluck('emoji')->toArray();
        $this->assertContains('👍', $emojis);
        $this->assertContains('❤️', $emojis);
    }

    public function test_reply_comment_reactions_relationship_works()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $replyComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
        ]);

        // Create reactions
        CommentReaction::create([
            'comment_id' => $replyComment->id,
            'user_id' => $user->id,
            'emoji' => '👍',
        ]);

        CommentReaction::create([
            'comment_id' => $replyComment->id,
            'user_id' => $user->id,
            'emoji' => '❤️',
        ]);

        // Test relationship
        $this->assertEquals(2, $replyComment->reactions()->count());
        $this->assertEquals(1, $replyComment->reactions()->where('emoji', '👍')->count());
        $this->assertEquals(1, $replyComment->reactions()->where('emoji', '❤️')->count());
    }

    public function test_reply_comment_reactions_are_separate_from_parent_comment()
    {
        // Create test data
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $parentComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => null,
        ]);

        $replyComment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'parent_id' => $parentComment->id,
        ]);

        // Create reactions on parent comment
        CommentReaction::create([
            'comment_id' => $parentComment->id,
            'user_id' => $user->id,
            'emoji' => '👍',
        ]);

        // Create reactions on reply comment
        CommentReaction::create([
            'comment_id' => $replyComment->id,
            'user_id' => $user->id,
            'emoji' => '❤️',
        ]);

        // Test that reactions are separate
        $this->assertEquals(1, $parentComment->reactions()->count());
        $this->assertEquals(1, $replyComment->reactions()->count());

        $this->assertEquals(0, $parentComment->reactions()->where('emoji', '❤️')->count());
        $this->assertEquals(0, $replyComment->reactions()->where('emoji', '👍')->count());
    }
}
