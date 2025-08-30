<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileExtensions = ['pdf', 'docx', 'xlsx', 'pptx', 'txt', 'jpg', 'png'];
        $type = fake()->randomElement(['internal', 'external']);

        return [
            'title' => fake()->sentence(3),
            'type' => $type,
            'url' => $type === 'external' ? fake()->url() : null,
            'file_path' => $type === 'internal' ? 'documents/'.fake()->uuid().'.'.fake()->randomElement($fileExtensions) : null,
            'project_id' => null, // Will be set when creating
            'notes' => fake()->paragraph(),
            'updated_by' => null, // Will be set when creating
        ];
    }
}
