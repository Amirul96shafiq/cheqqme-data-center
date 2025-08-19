<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(3),
            'status' => $this->faker->randomElement(['todo', 'in_progress', 'toreview', 'completed', 'archived']),
            'due_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'assigned_to' => User::factory(),
            'client' => Client::factory(),
            'project' => [Project::factory()->create()->id],
            'document' => [],
            'important_url' => [],
            'extra_information' => [],
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }
}
