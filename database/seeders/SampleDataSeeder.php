<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Comment;
use App\Models\Document;
use App\Models\ImportantUrl;
use App\Models\PhoneNumber;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create additional users
        $users = User::factory(5)->create();
        $adminUser = User::first(); // Use the existing test user

        // Create clients
        $clients = Client::factory(3)->create([
            'updated_by' => $adminUser->id,
        ]);

        // Create projects
        $projects = collect();
        foreach ($clients as $client) {
            $projectCount = rand(1, 3);
            $clientProjects = Project::factory($projectCount)->create([
                'client_id' => $client->id,
                'updated_by' => $adminUser->id,
            ]);
            $projects = $projects->merge($clientProjects);
        }

        // Create documents
        $documents = collect();
        foreach ($projects as $project) {
            $docCount = rand(1, 2);
            $projectDocs = Document::factory($docCount)->create([
                'project_id' => $project->id,
                'updated_by' => $adminUser->id,
            ]);
            $documents = $documents->merge($projectDocs);
        }

        // Create important URLs
        $importantUrls = collect();
        foreach ($clients as $client) {
            $urlCount = rand(1, 2);
            $clientUrls = ImportantUrl::factory($urlCount)->create([
                'client_id' => $client->id,
                'updated_by' => $adminUser->id,
            ]);
            $importantUrls = $importantUrls->merge($clientUrls);
        }

        // Create phone numbers
        $phoneNumbers = collect();
        foreach ($clients as $client) {
            $phoneCount = rand(1, 2);
            $clientPhones = PhoneNumber::factory($phoneCount)->create([
                'updated_by' => $adminUser->id,
            ]);
            $phoneNumbers = $phoneNumbers->merge($clientPhones);
        }

        // Create tasks
        $tasks = collect();
        foreach ($projects as $project) {
            $taskCount = rand(2, 5);
            $projectTasks = Task::factory($taskCount)->create([
                'project' => $project->title,
                'client' => $project->client_id,
                'assigned_to' => $users->random()->id,
                'updated_by' => $adminUser->id,
            ]);
            $tasks = $tasks->merge($projectTasks);
        }

        // Create comments for tasks
        foreach ($tasks as $task) {
            $commentCount = rand(1, 3);
            Comment::factory($commentCount)->create([
                'task_id' => $task->id,
                'user_id' => $users->random()->id,
            ]);
        }

        $this->command->info('Sample data created successfully!');
        $this->command->info('Users: '.User::count());
        $this->command->info('Clients: '.Client::count());
        $this->command->info('Projects: '.Project::count());
        $this->command->info('Documents: '.Document::count());
        $this->command->info('Important URLs: '.ImportantUrl::count());
        $this->command->info('Phone Numbers: '.PhoneNumber::count());
        $this->command->info('Tasks: '.Task::count());
        $this->command->info('Comments: '.Comment::count());
    }
}
