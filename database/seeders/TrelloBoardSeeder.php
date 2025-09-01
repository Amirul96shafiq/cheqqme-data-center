<?php

namespace Database\Seeders;

use App\Models\TrelloBoard;
use App\Models\User;
use Illuminate\Database\Seeder;

class TrelloBoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a default user for seeding
        $defaultUser = User::first() ?? User::factory()->create();

        $boards = [
            [
                'name' => 'Project Management',
                'url' => 'https://trello.com/b/project-management',
                'notes' => 'Main project management board for tracking all ongoing projects and their status.',
                'show_on_boards' => true,
                'extra_information' => [
                    [
                        'title' => 'Project Status',
                        'value' => 'Currently managing 5 active projects with 3 in planning phase.',
                    ],
                    [
                        'title' => 'Team Members',
                        'value' => 'Development team: 8 developers, 2 designers, 1 project manager.',
                    ],
                ],
            ],
            [
                'name' => 'Bug Tracking',
                'url' => 'https://trello.com/b/bug-tracking',
                'notes' => 'Centralized bug tracking and issue management for all applications.',
                'show_on_boards' => true,
                'extra_information' => [
                    [
                        'title' => 'Priority Levels',
                        'value' => 'Critical, High, Medium, Low priority system for bug categorization.',
                    ],
                ],
            ],
            [
                'name' => 'Feature Development',
                'url' => 'https://trello.com/b/feature-development',
                'notes' => 'Planning and development tracking for new features and enhancements.',
                'show_on_boards' => true,
                'extra_information' => [
                    [
                        'title' => 'Development Phases',
                        'value' => 'Planning → Design → Development → Testing → Deployment.',
                    ],
                    [
                        'title' => 'Release Schedule',
                        'value' => 'Bi-weekly releases with monthly major feature releases.',
                    ],
                ],
            ],
            [
                'name' => 'Marketing Campaigns',
                'url' => 'https://trello.com/b/marketing-campaigns',
                'notes' => 'Marketing campaign planning and execution tracking.',
                'show_on_boards' => false,
                'extra_information' => [
                    [
                        'title' => 'Campaign Types',
                        'value' => 'Social media, email marketing, content marketing, and paid advertising.',
                    ],
                ],
            ],
            [
                'name' => 'Customer Support',
                'url' => 'https://trello.com/b/customer-support',
                'notes' => 'Customer support ticket management and resolution tracking.',
                'show_on_boards' => true,
                'extra_information' => [
                    [
                        'title' => 'Response Time',
                        'value' => 'Target: 2 hours for critical issues, 24 hours for standard requests.',
                    ],
                ],
            ],
        ];

        foreach ($boards as $boardData) {
            TrelloBoard::create([
                ...$boardData,
                'created_by' => $defaultUser->id,
                'updated_by' => $defaultUser->id,
            ]);
        }

        $this->command->info('Trello Boards seeded successfully!');
    }
}
