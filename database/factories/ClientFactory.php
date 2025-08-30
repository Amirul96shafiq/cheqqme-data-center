<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => $this->faker->company(),
            'pic_name' => $this->faker->name(),
            'pic_email' => $this->faker->email(),
            'pic_contact_number' => $this->faker->randomElement([
                // Malaysia: +60 1X-XXX XXXX (mobile) or +60 3-XXXX XXXX (landline)
                '+60'.$this->faker->randomElement([
                    '1'.$this->faker->randomElement(['0', '1', '2', '3', '4', '5', '6', '7', '8', '9']).$this->faker->numerify('#######'),
                    '3'.$this->faker->numerify('########'),
                    '4'.$this->faker->numerify('########'),
                    '5'.$this->faker->numerify('########'),
                    '6'.$this->faker->numerify('########'),
                    '7'.$this->faker->numerify('########'),
                    '8'.$this->faker->numerify('########'),
                    '9'.$this->faker->numerify('########'),
                ]),
                // Indonesia: +62 8XX-XXX-XXXX (mobile) or +62 2X-XXX-XXXX (landline)
                '+62'.$this->faker->randomElement([
                    '8'.$this->faker->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']).$this->faker->numerify('#######'),
                    '2'.$this->faker->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']).$this->faker->numerify('#######'),
                    '3'.$this->faker->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']).$this->faker->numerify('#######'),
                    '4'.$this->faker->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']).$this->faker->numerify('#######'),
                    '5'.$this->faker->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']).$this->faker->numerify('#######'),
                    '6'.$this->faker->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']).$this->faker->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']).$this->faker->numerify('######'),
                    '7'.$this->faker->randomElement(['1', '2', '3', '4', '5', '6', '7', '8', '9']).$this->faker->numerify('#######'),
                ]),
                // Singapore: +65 9XXXXXXX (mobile) or +65 6XXXXXXX (landline)
                '+65'.$this->faker->randomElement([
                    '9'.$this->faker->numerify('#######'),
                    '6'.$this->faker->numerify('#######'),
                    '3'.$this->faker->numerify('#######'),
                ]),
            ]),
            'company_address' => $this->faker->address(),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return $attributes['created_at'];
            },
        ];
    }
}
