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
        // User::factory(10)->create();

        // Create the specific user (will update if exists)
        User::updateOrCreate(
            ['email' => 'amirul96shafiq.harun@gmail.com'],
            [
                'name' => 'Amirul Shafiq Harun',
                'username' => 'Amirul Shafiq Harun',
                'password' => bcrypt(env('ADMIN_PASSWORD', 'ChangeThisPassword123!')),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'username' => 'Test User',
                'password' => bcrypt(env('TEST_USER_PASSWORD', 'TestPassword123!')),
                'email_verified_at' => now(),
            ]
        );

        // Call the sample data seeder to populate other tables
        $this->call([
            SampleDataSeeder::class,
            TrelloBoardSeeder::class,
        ]);
    }
}
