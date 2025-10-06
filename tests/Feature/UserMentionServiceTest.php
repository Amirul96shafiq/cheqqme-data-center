<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use App\Services\UserMentionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserMentionServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserMentionService $userMentionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userMentionService = new UserMentionService;
    }

    /** @test */
    public function it_can_extract_mentions_from_comment_text()
    {
        // Create test users
        $user1 = User::factory()->create(['username' => 'john_doe', 'name' => 'John Doe']);
        $user2 = User::factory()->create(['username' => 'jane_smith', 'name' => 'Jane Smith']);

        $commentText = 'Hey @john_doe and @jane_smith, check this out!';

        $mentions = $this->userMentionService->extractMentions($commentText);

        $this->assertCount(2, $mentions);
        $this->assertContains($user1->id, $mentions);
        $this->assertContains($user2->id, $mentions);
    }

    /** @test */
    public function it_can_extract_everyone_mention()
    {
        $commentText = 'Hey @everyone, important announcement!';

        $mentions = $this->userMentionService->extractMentions($commentText);

        $this->assertCount(1, $mentions);
        $this->assertContains('@Everyone', $mentions);
    }

    /** @test */
    public function it_can_extract_mentions_by_display_name()
    {
        $user = User::factory()->create(['username' => 'john_doe', 'name' => 'John Doe']);

        $commentText = 'Hey @John Doe, how are you?';

        $mentions = $this->userMentionService->extractMentions($commentText);

        $this->assertCount(1, $mentions);
        $this->assertContains($user->id, $mentions);
    }

    /** @test */
    public function it_handles_multiple_word_names()
    {
        $user = User::factory()->create(['username' => 'john_doe', 'name' => 'John Michael Doe']);

        $commentText = 'Hey @John Michael Doe, check this out!';

        $mentions = $this->userMentionService->extractMentions($commentText);

        $this->assertCount(1, $mentions);
        $this->assertContains($user->id, $mentions);
    }

    /** @test */
    public function it_ignores_invalid_mentions()
    {
        $commentText = 'Hey @nonexistent_user and @another_fake_user!';

        $mentions = $this->userMentionService->extractMentions($commentText);

        $this->assertEmpty($mentions);
    }

    /** @test */
    public function it_handles_empty_comment_text()
    {
        $mentions = $this->userMentionService->extractMentions('');

        $this->assertEmpty($mentions);
    }

    /** @test */
    public function it_handles_html_in_comment_text()
    {
        $user = User::factory()->create(['username' => 'john_doe']);

        $commentText = '<p>Hey <strong>@john_doe</strong>, check this out!</p>';

        $mentions = $this->userMentionService->extractMentions($commentText);

        $this->assertCount(1, $mentions);
        $this->assertContains($user->id, $mentions);
    }

    /** @test */
    public function it_can_get_users_for_mention_search()
    {
        // Create test users
        User::factory()->count(5)->create();

        $users = $this->userMentionService->getUsersForMentionSearch();

        $this->assertCount(5, $users);
        $this->assertArrayHasKey('id', $users->first());
        $this->assertArrayHasKey('username', $users->first());
        $this->assertArrayHasKey('email', $users->first());
        $this->assertArrayHasKey('name', $users->first());
    }

    /** @test */
    public function it_can_render_comment_with_mentions()
    {
        $user = User::factory()->create(['username' => 'john_doe', 'name' => 'John Doe']);
        $task = Task::factory()->create();
        $commentAuthor = User::factory()->create();

        $comment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $commentAuthor->id,
            'comment' => 'Hey @john_doe, check this out!',
            'mentions' => [$user->id],
        ]);

        $rendered = $this->userMentionService->renderCommentWithMentions($comment);

        $this->assertStringContainsString('<span class="mention">@john_doe</span>', $rendered);
    }

    /** @test */
    public function it_can_get_mentioned_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $task = Task::factory()->create();
        $commentAuthor = User::factory()->create();

        $comment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $commentAuthor->id,
            'mentions' => [$user1->id, $user2->id],
        ]);

        $mentionedUsers = $this->userMentionService->getMentionedUsers($comment);

        $this->assertCount(2, $mentionedUsers);
        $this->assertTrue($mentionedUsers->contains('id', $user1->id));
        $this->assertTrue($mentionedUsers->contains('id', $user2->id));
    }

    /** @test */
    public function it_handles_comment_without_mentions()
    {
        $task = Task::factory()->create();
        $commentAuthor = User::factory()->create();

        $comment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $commentAuthor->id,
            'mentions' => null,
        ]);

        $mentionedUsers = $this->userMentionService->getMentionedUsers($comment);

        $this->assertEmpty($mentionedUsers);
    }

    /** @test */
    public function it_validates_mentions_array()
    {
        $reflection = new \ReflectionClass($this->userMentionService);
        $method = $reflection->getMethod('validateMentions');
        $method->setAccessible(true);

        $mentions = ['@Everyone', 1, 2, 'invalid', 0, -1];

        $validMentions = $method->invoke($this->userMentionService, $mentions);

        $this->assertCount(3, $validMentions);
        $this->assertContains('@Everyone', $validMentions);
        $this->assertContains(1, $validMentions);
        $this->assertContains(2, $validMentions);
        $this->assertNotContains('invalid', $validMentions);
        $this->assertNotContains(0, $validMentions);
        $this->assertNotContains(-1, $validMentions);
    }

    /** @test */
    public function it_handles_deleted_comment_rendering()
    {
        $task = Task::factory()->create();
        $commentAuthor = User::factory()->create(['username' => 'test_user']);

        $comment = Comment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $commentAuthor->id,
            'status' => 'deleted',
            'comment' => 'This comment was deleted',
        ]);

        $rendered = $this->userMentionService->renderCommentWithMentions($comment);

        $this->assertStringContainsString('test_user has deleted this comment', $rendered);
    }
}
