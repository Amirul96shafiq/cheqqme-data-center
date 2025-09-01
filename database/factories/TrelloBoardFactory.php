<?php

namespace Database\Factories;

use App\Models\TrelloBoard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrelloBoard>
 */
class TrelloBoardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TrelloBoard::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true).' Board',
            'url' => fake()->url(),
            'notes' => fake()->optional(0.7)->paragraph(3),
            'show_on_boards' => fake()->boolean(80), // 80% chance to be true
            'extra_information' => fake()->optional(0.5)->randomElements([
                ['title' => 'Project Status', 'value' => fake()->paragraph()],
                ['title' => 'Team Members', 'value' => fake()->paragraph()],
                ['title' => 'Milestones', 'value' => fake()->paragraph()],
                ['title' => 'Notes', 'value' => fake()->paragraph()],
            ], fake()->numberBetween(1, 3)),
            'created_by' => User::factory(),
            'updated_by' => fake()->optional(0.3)->randomElement([User::factory(), null]),
        ];
    }

    /**
     * Indicate that the board should be shown on boards.
     */
    public function visible(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_on_boards' => true,
        ]);
    }

    /**
     * Indicate that the board should be hidden from boards.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'show_on_boards' => false,
        ]);
    }

    /**
     * Indicate that the board has extra information.
     */
    public function withExtraInfo(): static
    {
        return $this->state(fn (array $attributes) => [
            'extra_information' => [
                ['title' => 'Project Overview', 'value' => fake()->paragraph()],
                ['title' => 'Key Features', 'value' => fake()->paragraph()],
                ['title' => 'Timeline', 'value' => fake()->paragraph()],
            ],
        ]);
    }
}
