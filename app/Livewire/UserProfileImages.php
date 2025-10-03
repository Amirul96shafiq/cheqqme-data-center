<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class UserProfileImages extends Component
{
    public User $user;

    public function mount()
    {
        $this->user = auth()->user();
    }

    public function render()
    {
        return view('livewire.user-profile-images');
    }
}
