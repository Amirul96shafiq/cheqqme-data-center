<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class UserProfileImages extends Component
{
    use WithFileUploads;

    public User $user;
    public $avatar;
    public $coverImage;
    public $avatarPreview;
    public $coverImagePreview;

    public function mount()
    {
        $this->user = auth()->user();
    }

    public function updatedAvatar()
    {
        $this->validate([
            'avatar' => 'image|max:2048|dimensions:max_width=1024,max_height=1024',
        ]);

        $this->avatarPreview = $this->avatar->temporaryUrl();
    }

    public function updatedCoverImage()
    {
        $this->validate([
            'coverImage' => 'image|max:4096|dimensions:min_width=800,max_width=2000',
        ]);

        $this->coverImagePreview = $this->coverImage->temporaryUrl();
    }

    public function saveAvatar()
    {
        if (!$this->avatar) {
            return;
        }

        $this->validate([
            'avatar' => 'image|max:2048|dimensions:max_width=1024,max_height=1024',
        ]);

        // Delete old avatar if exists
        if ($this->user->avatar) {
            Storage::disk('public')->delete($this->user->avatar);
        }

        // Store new avatar
        $path = $this->avatar->store('avatars', 'public');
        
        $this->user->update(['avatar' => $path]);
        
        $this->avatar = null;
        $this->avatarPreview = null;
        
        $this->dispatch('avatar-updated');
        
        $this->dispatch('notify', 
            type: 'success',
            title: 'Avatar Updated',
            message: 'Your profile avatar has been updated successfully!'
        );
    }

    public function saveCoverImage()
    {
        if (!$this->coverImage) {
            return;
        }

        $this->validate([
            'coverImage' => 'image|max:4096|dimensions:min_width=800,max_width=2000',
        ]);

        // Delete old cover image if exists
        if ($this->user->cover_image) {
            Storage::disk('public')->delete($this->user->cover_image);
        }

        // Store new cover image
        $path = $this->coverImage->store('covers', 'public');
        
        $this->user->update(['cover_image' => $path]);
        
        $this->coverImage = null;
        $this->coverImagePreview = null;
        
        $this->dispatch('cover-image-updated');
        
        $this->dispatch('notify', 
            type: 'success',
            title: 'Cover Image Updated',
            message: 'Your profile cover image has been updated successfully!'
        );
    }

    public function removeAvatar()
    {
        // Delete current avatar
        if ($this->user->avatar) {
            Storage::disk('public')->delete($this->user->avatar);
            $this->user->update(['avatar' => null]);
            
            $this->dispatch('avatar-updated');
            
            $this->dispatch('notify', 
                type: 'success',
                title: 'Avatar Removed',
                message: 'Your custom avatar has been removed.'
            );
        }
    }

    public function removeCoverImage()
    {
        // Delete current cover image
        if ($this->user->cover_image) {
            Storage::disk('public')->delete($this->user->cover_image);
            $this->user->update(['cover_image' => null]);
            
            $this->dispatch('cover-image-updated');
            
            $this->dispatch('notify', 
                type: 'success',
                title: 'Cover Image Removed',
                message: 'Your custom cover image has been removed.'
            );
        }
    }

    public function render()
    {
        return view('livewire.user-profile-images');
    }
}
