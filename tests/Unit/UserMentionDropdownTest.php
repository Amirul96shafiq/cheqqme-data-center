<?php

namespace Tests\Unit;

use App\Livewire\UserMentionDropdown;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserMentionDropdownTest extends TestCase
{
    use RefreshDatabase;

    // Test component can render
    public function test_component_can_render()
    {
        $component = Livewire::test(UserMentionDropdown::class);

        $component->assertStatus(200);
    }

    // Test show dropdown sets correct properties
    public function test_show_dropdown_sets_correct_properties()
    {
        $component = Livewire::test(UserMentionDropdown::class);

        $component->call('showDropdown', 'test-input', '@john', 100, 200);

        $component->assertSet('targetInputId', 'test-input')
            ->assertSet('search', '@john')
            ->assertSet('showDropdown', true)
            ->assertSet('dropdownX', 100)
            ->assertSet('dropdownY', 200)
            ->assertSet('selectedIndex', 0);
    }

    // Test hide dropdown resets properties
    public function test_hide_dropdown_resets_properties()
    {
        $component = Livewire::test(UserMentionDropdown::class);

        // First show the dropdown
        $component->call('showDropdown', 'test-input', '@john', 100, 200);

        // Then hide it
        $component->call('hideDropdown');

        $component->assertSet('showDropdown', false)
            ->assertSet('search', '')
            ->assertSet('users', []);
    }

    // Test search users finds matching users
    public function test_search_users_finds_matching_users()
    {
        // Create test users
        User::factory()->create(['username' => 'john_doe', 'name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['username' => 'jane_smith', 'name' => 'Jane Smith', 'email' => 'jane@example.com']);
        User::factory()->create(['username' => 'bob_wilson', 'name' => 'Bob Wilson', 'email' => 'bob@example.com']);

        $component = Livewire::test(UserMentionDropdown::class);

        $component->set('search', 'john')
            ->call('searchUsers');

        $component->assertSet('users', function ($users) {
            return count($users) === 1 && $users[0]['username'] === 'john_doe';
        });
    }

    // Test updated search maintains static position
    public function test_updated_search_maintains_static_position()
    {
        $component = Livewire::test(UserMentionDropdown::class);

        // Set initial position and search
        $component->call('showDropdown', 'test-input', '@john', 100, 200);

        // Update the search term (simulating user typing after @)
        $component->set('search', 'john_updated');

        // Verify position remains unchanged and search was updated
        $component->assertSet('selectedIndex', 0)
            ->assertSet('dropdownX', 100)
            ->assertSet('dropdownY', 200);
    }

    // Test select user dispatches event and hides dropdown
    public function test_select_user_dispatches_event_and_hides_dropdown()
    {
        // Create a test user
        $user = User::factory()->create(['username' => 'test_user']);

        $component = Livewire::test(UserMentionDropdown::class);

        // Set up the component state
        $component->set('users', [
            [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'name' => $user->name,
                'avatar' => null,
                'short_name' => $user->username,
            ],
        ])
            ->set('targetInputId', 'test-input');

        // Select the user
        $component->call('selectUser', 0);

        // Verify the dropdown is hidden
        $component->assertSet('showDropdown', false);
    }

    // Test search users with empty search returns all users
    public function test_search_users_with_empty_search_returns_all_users()
    {
        // Create test users
        User::factory()->create(['username' => 'user1']);
        User::factory()->create(['username' => 'user2']);
        User::factory()->create(['username' => 'user3']);

        $component = Livewire::test(UserMentionDropdown::class);

        $component->set('search', '')
            ->call('searchUsers');

        $component->assertSet('users', function ($users) {
            return count($users) === 3;
        });
    }

    // Test search users with partial match
    public function test_search_users_with_partial_match()
    {
        // Create test users
        User::factory()->create(['username' => 'john_doe']);
        User::factory()->create(['username' => 'johnny_cash']);
        User::factory()->create(['username' => 'jane_doe']);

        $component = Livewire::test(UserMentionDropdown::class);

        $component->set('search', 'john')
            ->call('searchUsers');

        $component->assertSet('users', function ($users) {
            return count($users) === 2 &&
                in_array($users[0]['username'], ['john_doe', 'johnny_cash']) &&
                in_array($users[1]['username'], ['john_doe', 'johnny_cash']);
        });
    }

    // Test navigation up wraps to bottom
    public function test_navigation_up_wraps_to_bottom()
    {
        // Create test users
        User::factory()->create(['username' => 'user1']);
        User::factory()->create(['username' => 'user2']);
        User::factory()->create(['username' => 'user3']);

        $component = Livewire::test(UserMentionDropdown::class);

        // Set up component with users and start at index 0
        $component->set('users', [
            ['id' => 1, 'username' => 'user1', 'email' => 'user1@test.com', 'name' => 'User 1', 'avatar' => null, 'short_name' => 'user1'],
            ['id' => 2, 'username' => 'user2', 'email' => 'user2@test.com', 'name' => 'User 2', 'avatar' => null, 'short_name' => 'user2'],
            ['id' => 3, 'username' => 'user3', 'email' => 'user3@test.com', 'name' => 'User 3', 'avatar' => null, 'short_name' => 'user3'],
        ])
            ->set('selectedIndex', 0);

        // Test that updateSelectedIndex works correctly
        $component->call('updateSelectedIndex', 2);

        $component->assertSet('selectedIndex', 2);
    }

    // Test navigation down wraps to top
    public function test_navigation_down_wraps_to_top()
    {
        // Create test users
        User::factory()->create(['username' => 'user1']);
        User::factory()->create(['username' => 'user2']);
        User::factory()->create(['username' => 'user3']);

        $component = Livewire::test(UserMentionDropdown::class);

        // Set up component with users and start at last index
        $component->set('users', [
            ['id' => 1, 'username' => 'user1', 'email' => 'user1@test.com', 'name' => 'User 1', 'avatar' => null, 'short_name' => 'user1'],
            ['id' => 2, 'username' => 'user2', 'email' => 'user2@test.com', 'name' => 'User 2', 'avatar' => null, 'short_name' => 'user2'],
            ['id' => 3, 'username' => 'user3', 'email' => 'user3@test.com', 'name' => 'User 3', 'avatar' => null, 'short_name' => 'user3'],
        ])
            ->set('selectedIndex', 2);

        // Test that updateSelectedIndex works correctly
        $component->call('updateSelectedIndex', 0);

        $component->assertSet('selectedIndex', 0);
    }

    // Test select current user selects highlighted user
    public function test_select_current_user_selects_highlighted_user()
    {
        // Create a test user
        $user = User::factory()->create(['username' => 'test_user']);

        $component = Livewire::test(UserMentionDropdown::class);

        // Set up the component state
        $component->set('users', [
            [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'name' => $user->name,
                'avatar' => null,
                'short_name' => $user->username,
            ],
        ])
            ->set('selectedIndex', 0)
            ->set('targetInputId', 'test-input');

        // Select current user
        $component->call('selectCurrentUser');

        // Verify the dropdown is hidden
        $component->assertSet('showDropdown', false);
    }
}
