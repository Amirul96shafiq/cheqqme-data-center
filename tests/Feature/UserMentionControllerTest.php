<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserMentionControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_search_users_for_mentions()
    {
        // Create test users
        User::factory()->create(['username' => 'john_doe', 'name' => 'John Doe']);
        User::factory()->create(['username' => 'jane_smith', 'name' => 'Jane Smith']);

        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/users/mention-search');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'users' => [
                    '*' => [
                        'id',
                        'username',
                        'email',
                        'name',
                        'avatar',
                        'default_avatar',
                    ],
                ],
                'count',
            ]);

        $this->assertCount(3, $response->json('users')); // 2 created + 1 authenticated user
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/users/mention-search');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_search_parameters()
    {
        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/users/mention-search?search='.str_repeat('a', 101));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['search']);
    }

    /** @test */
    public function it_validates_limit_parameter()
    {
        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/users/mention-search?limit=501');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['limit']);
    }

    /** @test */
    public function it_handles_errors_gracefully()
    {
        // Mock a service that throws an exception
        $this->mock(\App\Services\UserMentionService::class)
            ->shouldReceive('getUsersForMentionSearch')
            ->andThrow(new \Exception('Database error'));

        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/users/mention-search');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Failed to fetch users for mention search',
            ]);
    }

    /** @test */
    public function it_returns_users_ordered_by_username()
    {
        $authenticatedUser = User::factory()->create(['username' => 'authenticated_user']);
        User::factory()->create(['username' => 'zebra_user']);
        User::factory()->create(['username' => 'alpha_user']);
        User::factory()->create(['username' => 'beta_user']);

        $response = $this->actingAs($authenticatedUser)
            ->getJson('/api/users/mention-search');

        $usernames = collect($response->json('users'))->pluck('username')->toArray();

        // Check that users are ordered alphabetically (excluding the authenticated user)
        $this->assertContains('alpha_user', $usernames);
        $this->assertContains('beta_user', $usernames);
        $this->assertContains('zebra_user', $usernames);
        $this->assertContains('authenticated_user', $usernames);

        // Verify the order is correct
        $this->assertGreaterThan(
            array_search('alpha_user', $usernames),
            array_search('beta_user', $usernames)
        );
        $this->assertGreaterThan(
            array_search('beta_user', $usernames),
            array_search('zebra_user', $usernames)
        );
    }

    /** @test */
    public function it_includes_default_avatar_for_users_without_avatar()
    {
        $user = User::factory()->create(['username' => 'test_user', 'avatar' => null]);

        $response = $this->actingAs(User::factory()->create())
            ->getJson('/api/users/mention-search');

        $userData = collect($response->json('users'))
            ->firstWhere('username', 'test_user');

        $this->assertNotNull($userData['default_avatar']);
        $this->assertStringContainsString('ui-avatars.com', $userData['default_avatar']);
    }
}
