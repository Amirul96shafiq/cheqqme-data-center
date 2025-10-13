<?php

namespace Database\Factories;

use App\Models\MeetingLink;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeetingLinkFactory extends Factory
{
    protected $model = MeetingLink::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'client_ids' => null,
            'project_id' => Project::factory(),
            'document_ids' => null,
            'meeting_platform' => fake()->randomElement(['Google Meet', 'Zoom Meeting', 'Teams Meeting']),
            'meeting_url' => null,
            'meeting_id' => null,
            'notes' => fake()->paragraph(),
            'extra_information' => [],
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    public function withGoogleMeet(): static
    {
        return $this->state(fn (array $attributes) => [
            'meeting_platform' => 'Google Meet',
            'meeting_url' => 'https://meet.google.com/'.fake()->regexify('[a-z]{3}-[a-z]{4}-[a-z]{3}'),
            'meeting_id' => fake()->uuid(),
        ]);
    }
}

