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

  #[On('showMentionDropdown')]
  public function showDropdown(string $inputId, string $searchTerm = '', int $x = 0, int $y = 0)
  {
    \Log::info('UserMentionDropdown::showDropdown called', [
      'inputId' => $inputId,
      'searchTerm' => $searchTerm,
      'x' => $x,
      'y' => $y,
    ]);

    $this->targetInputId = $inputId;
    $this->search = $searchTerm;
    $this->showDropdown = true;
    $this->selectedIndex = 0;

    // Set dropdown position using x and y coordinates
    $this->dropdownX = $x;
    $this->dropdownY = $y;

    $this->searchUsers();
  }

  #[On('hideMentionDropdown')]
  public function hideDropdown()
  {
    \Log::info('UserMentionDropdown::hideDropdown called');
    $this->showDropdown = false;
    $this->search = '';
    $this->users = [];
  }

  // Arrow key navigation removed as requested

  public function searchUsers()
  {
    \Log::info('UserMentionDropdown::searchUsers called', ['search' => $this->search]);

    $query = User::query()
      ->where('username', 'like', '%' . $this->search . '%')
      ->orWhere('email', 'like', '%' . $this->search . '%')
      ->orWhere('name', 'like', '%' . $this->search . '%')
      ->orderBy('username')
      ->limit(10);

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

    \Log::info('UserMentionDropdown::searchUsers completed', ['userCount' => count($this->users)]);
  }

  public function selectUser(int $index)
  {
    \Log::info('UserMentionDropdown::selectUser called', [
      'index' => $index,
      'totalUsers' => count($this->users),
      'targetInputId' => $this->targetInputId,
      'users' => $this->users,
    ]);

    if (isset($this->users[$index])) {
      $user = $this->users[$index];

      \Log::info('User found for selection', [
        'user' => $user,
        'username' => $user['username'],
        'inputId' => $this->targetInputId,
      ]);

      $this->dispatch('userSelected', username: $user['username'], inputId: $this->targetInputId);

      \Log::info('userSelected event dispatched', ['username' => $user['username']]);

      $this->hideDropdown();
    } else {
      \Log::warning('User not found at index', ['index' => $index, 'users' => $this->users]);
    }
  }

  public function updatedSearch()
  {
    $this->selectedIndex = 0;
    $this->searchUsers();

    // Position remains static at the @ symbol - no recalculation needed
  }

  public function render()
  {
    return view('livewire.user-mention-dropdown');
  }
}
