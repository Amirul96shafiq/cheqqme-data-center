<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use App\Filament\Pages\Base\BaseCreateRecord;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class CreateUser extends BaseCreateRecord
{
    protected static string $resource = UserResource::class;

    protected function handleRecordCreation(array $data): User
    {
        // Check if a soft-deleted user exists with the same email
        $existingUser = User::onlyTrashed()
            ->where('email', $data['email'])
            ->first();

        if ($existingUser) {
            // Restore and update info
            $existingUser->restore();
            $existingUser->update([
                'name' => $data['name'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
            ]);

            return $existingUser;
        }

        // No soft-deleted user — proceed normally
        return User::create($data);
    }
}
