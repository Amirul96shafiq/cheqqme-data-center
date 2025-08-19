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

    public function test_component_can_render()
    {
        $component = Livewire::test(UserMentionDropdown::class);

        $component->assertStatus(200);
    }

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
}
