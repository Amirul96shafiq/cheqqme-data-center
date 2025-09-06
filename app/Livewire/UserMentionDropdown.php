<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class UserMentionDropdown extends Component
{
    public string $search = '';

    public array $users = [];

    public bool $showDropdown = false;

    public int $selectedIndex = 0;

    public string $targetInputId = '';

    public int $dropdownX = 0;

    public int $dropdownY = 0;

    public function mount()
    {
        // Component initialization
    }

    // Show the dropdown
    #[On('showMentionDropdown')]
    public function showDropdown(string $inputId, string $searchTerm = '', int $x = 0, int $y = 0)
    {
        // Log the event
        \Log::info('UserMentionDropdown::showDropdown called', [
            'inputId' => $inputId,
            'searchTerm' => $searchTerm,
            'x' => $x,
            'y' => $y,
        ]);

        $this->targetInputId = $inputId; // Set the target input id
        $this->search = $searchTerm; // Set the search term
        $this->showDropdown = true; // Show the dropdown
        $this->selectedIndex = 0; // Set the selected index

        // Set dropdown position using x and y coordinates
        $this->dropdownX = $x;
        $this->dropdownY = $y;

        $this->searchUsers(); // Search for users
    }

    // Hide the dropdown
    #[On('hideMentionDropdown')]
    public function hideDropdown()
    {
        // Log the event
        \Log::info('UserMentionDropdown::hideDropdown called');
        $this->showDropdown = false;
        $this->search = '';
        $this->users = [];
    }

    // Search for users
    public function searchUsers()
    {
        // Log the event
        \Log::info('UserMentionDropdown::searchUsers called', ['search' => $this->search]);

        // Clean the search term - remove @ symbol if present
        $cleanSearch = ltrim($this->search, '@');

        $users = [];

        // Check if search matches "@all" (case insensitive)
        if (empty($cleanSearch) || stripos('all', $cleanSearch) === 0) {
            // Add @all as the first option
            $users[] = [
                'id' => '@all',
                'username' => 'all',
                'email' => 'Notify all users',
                'name' => 'All Users',
                'avatar' => null,
                'short_name' => 'all',
                'is_special' => true, // Mark as special for styling
            ];
        }

        // Search for regular users
        $query = User::query()
            ->where('username', 'like', '%' . $cleanSearch . '%')
            ->orWhere('email', 'like', '%' . $cleanSearch . '%')
            ->orWhere('name', 'like', '%' . $cleanSearch . '%')
            ->orderBy('username')
            ->limit(10);

        // Log the actual query for debugging
        \Log::info('UserMentionDropdown::searchUsers query', [
            'original_search' => $this->search,
            'cleaned_search' => $cleanSearch,
            'sql' => $query->toSql(),
        ]);

        // Get the regular users and merge with @all
        $regularUsers = $query->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'short_name' => $user->short_name ?? $user->username,
                'is_special' => false,
            ];
        })->toArray();

        $this->users = array_merge($users, $regularUsers);

        // Log the event
        \Log::info('UserMentionDropdown::searchUsers completed', ['userCount' => count($this->users)]);
    }

    // Select a user
    public function selectUser(int $index)
    {
        // Log the event
        \Log::info('UserMentionDropdown::selectUser called', [
            'index' => $index,
            'totalUsers' => count($this->users),
            'targetInputId' => $this->targetInputId,
            'users' => $this->users,
        ]);

        // Check if the user exists
        if (isset($this->users[$index])) {
            $user = $this->users[$index]; // Get the user

            // Log the event
            \Log::info('User found for selection', [
                'user' => $user,
                'username' => $user['username'],
                'inputId' => $this->targetInputId,
            ]);

            // Handle special @all case
            if ($user['id'] === '@all') {
                // Dispatch the userSelected event with special @all identifier
                $this->dispatch('userSelected', username: 'all', userId: '@all', inputId: $this->targetInputId);
                \Log::info('userSelected event dispatched for @all');
            } else {
                // Dispatch the userSelected event for regular users
                $this->dispatch('userSelected', username: $user['username'], userId: $user['id'], inputId: $this->targetInputId);
                \Log::info('userSelected event dispatched', ['username' => $user['username']]);
            }

            // Hide the dropdown immediately
            $this->hideDropdown();
        } else {
            \Log::warning('User not found at index', ['index' => $index, 'users' => $this->users]);
        }
    }

    // Handle userSelected event from Alpine.js
    #[On('userSelected')]
    public function onUserSelected(string $username, int|string $userId, string $inputId)
    {
        // Log the event
        \Log::info('UserMentionDropdown::onUserSelected called', [
            'username' => $username,
            'userId' => $userId,
            'inputId' => $inputId,
        ]);

        // Hide the dropdown immediately when user is selected
        $this->hideDropdown();
    }

    // Update the search
    public function updatedSearch()
    {
        $this->selectedIndex = 0; // Set the selected index
        $this->searchUsers(); // Search for users

    }

    // Select the current user
    #[On('selectCurrentUser')]
    public function selectCurrentUser()
    {
        if (isset($this->users[$this->selectedIndex])) {
            $this->selectUser($this->selectedIndex);
        }
    }

    // Update the selected index (non-blocking for client-side navigation)
    #[On('updateSelectedIndex')]
    public function updateSelectedIndex(int $index)
    {
        // Update the selected index from client-side navigation
        // This is non-blocking and only for state synchronization
        if ($index >= 0 && $index < count($this->users)) {
            $this->selectedIndex = $index;
        }
    }

    // Render the component
    public function render()
    {
        return view('livewire.user-mention-dropdown');
    }
}
