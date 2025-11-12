<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create the specific user (will update if exists)
        User::updateOrCreate(
            ['email' => 'amirul96shafiq.harun@gmail.com'],
            [
                'name' => 'Amirul Shafiq Harun',
                'username' => 'Amirul96shafiq',
                'password' => bcrypt(\Illuminate\Support\Str::random(12)),
                'online_status' => 'invisible',
                'email_verified_at' => now(),
            ]
        );
    }
}
