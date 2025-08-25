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

        // Search for users
        $query = User::query()
            ->where('username', 'like', '%'.$this->search.'%')
            ->orWhere('email', 'like', '%'.$this->search.'%')
            ->orWhere('name', 'like', '%'.$this->search.'%')
            ->orderBy('username')
            ->limit(10);

        // Get the users
        $this->users = $query->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'short_name' => $user->short_name ?? $user->username,
            ];
        })->toArray();

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

            // Dispatch the userSelected event
            $this->dispatch('userSelected', username: $user['username'], userId: $user['id'], inputId: $this->targetInputId);

            // Log the event
            \Log::info('userSelected event dispatched', ['username' => $user['username']]);

            // Hide the dropdown
            $this->hideDropdown();
        } else {
            \Log::warning('User not found at index', ['index' => $index, 'users' => $this->users]);
        }
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

    // Update the selected index
    #[On('updateSelectedIndex')]
    public function updateSelectedIndex(int $index)
    {
        // Update the selected index from client-side navigation
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
