<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImportantUrl>
 */
class ImportantUrlFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $urlTypes = [
            'https://drive.google.com/drive/folders/',
            'https://sharepoint.company.com/sites/',
            'https://figma.com/file/',
            'https://miro.com/app/board/',
            'https://trello.com/b/',
            'https://asana.com/projects/',
            'https://slack.com/app_redirect?channel=',
            'https://zoom.us/j/',
            'https://meet.google.com/',
            'https://github.com/',
        ];

        return [
            'title' => fake()->sentence(3),
            'url' => fake()->randomElement($urlTypes).fake()->regexify('[A-Za-z0-9]{10}'),
            'project_id' => null, // Will be set when creating
            'client_id' => null, // Will be set when creating
            'notes' => fake()->paragraph(),
            'updated_by' => null, // Will be set when creating
        ];
    }
}
