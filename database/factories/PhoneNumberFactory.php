<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PhoneNumber>
 */
class PhoneNumberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phoneTypes = [
            'Mobile',
            'Office',
            'Direct Line',
            'Emergency',
            'Support',
            'Sales',
            'Main Office',
            'Reception',
        ];

        return [
            'title' => fake()->randomElement($phoneTypes),
            'phone' => fake()->randomElement([
                // Malaysia: +60 1X-XXX XXXX (mobile) or +60 3-XXXX XXXX (landline)
                '+60' . fake()->randomElement([
                    '1' . fake()->randomElement(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']) . fake()->numerify('#######'),
                    '3' . fake()->numerify('########'),
                    '4' . fake()->numerify('########'),
                    '5' . fake()->numerify('########'),
                    '6' . fake()->numerify('########'),
                    '7' . fake()->numerify('########'),
                    '8' . fake()->numerify('########'),
                    '9' . fake()->numerify('########'),
                ]),
                // Indonesia: +62 8XX-XXX-XXXX (mobile) or +62 2X-XXX-XXXX (landline)
                '+62' . fake()->randomElement([
                    '8' . fake()->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']) . fake()->numerify('#######'),
                    '2' . fake()->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']) . fake()->numerify('#######'),
                    '3' . fake()->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']) . fake()->numerify('#######'),
                    '4' . fake()->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']) . fake()->numerify('#######'),
                    '5' . fake()->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']) . fake()->numerify('#######'),
                    '6' . fake()->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']) . fake()->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']) . fake()->numerify('######'),
                    '7' . fake()->numerify('#######'),
                ]),
                // Singapore: +65 9XXXXXXX (mobile) or +65 6XXXXXXX (landline)
                '+65' . fake()->randomElement([
                    '9' . fake()->numerify('#######'),
                    '6' . fake()->numerify('#######'),
                    '3' . fake()->numerify('#######'),
                ]),
            ]),
            'notes' => fake()->sentence(),
            'updated_by' => null, // Will be set when creating
        ];
    }
}
