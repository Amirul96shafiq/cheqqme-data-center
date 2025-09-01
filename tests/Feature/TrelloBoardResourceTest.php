<?php

namespace Tests\Feature;

use App\Models\TrelloBoard;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TrelloBoardResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up Filament admin panel
        Filament::setCurrentPanel('admin');
    }

    public function test_can_view_trello_board_list(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $trelloBoard = TrelloBoard::factory()->create();

        $this->get(route('filament.admin.resources.trello-boards.index'))
            ->assertSuccessful()
            ->assertSee($trelloBoard->name);
    }

    public function test_can_create_trello_board(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $boardData = [
            'name' => 'Test Board',
            'url' => 'https://trello.com/b/test-board',
            'notes' => 'Test notes for the board',
            'show_on_boards' => true,
        ];

        Livewire::test('filament.resources.trello-board-resource.pages.create-trello-board')
            ->fillForm($boardData)
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        $this->assertDatabaseHas('trello_boards', [
            'name' => 'Test Board',
            'url' => 'https://trello.com/b/test-board',
            'notes' => 'Test notes for the board',
            'show_on_boards' => true,
            'created_by' => $user->id,
        ]);
    }

    public function test_can_edit_trello_board(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $trelloBoard = TrelloBoard::factory()->create();

        $updatedData = [
            'name' => 'Updated Board Name',
            'notes' => 'Updated notes',
        ];

        Livewire::test('filament.resources.trello-board-resource.pages.edit-trello-board', [
            'record' => $trelloBoard,
        ])
            ->fillForm($updatedData)
            ->call('save')
            ->assertNotified();

        $this->assertDatabaseHas('trello_boards', [
            'id' => $trelloBoard->id,
            'name' => 'Updated Board Name',
            'notes' => 'Updated notes',
            'updated_by' => $user->id,
        ]);
    }

    public function test_can_delete_trello_board(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $trelloBoard = TrelloBoard::factory()->create();

        Livewire::test('filament.resources.trello-board-resource.pages.edit-trello-board', [
            'record' => $trelloBoard,
        ])
            ->callAction('delete')
            ->assertNotified();

        $this->assertSoftDeleted('trello_boards', [
            'id' => $trelloBoard->id,
        ]);
    }

    public function test_validation_requires_name_and_url(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        Livewire::test('filament.resources.trello-board-resource.pages.create-trello-board')
            ->fillForm([
                'name' => '',
                'url' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'url']);
    }

    public function test_notes_character_limit_enforced(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $longNotes = str_repeat('a', 501);

        Livewire::test('filament.resources.trello-board-resource.pages.create-trello-board')
            ->fillForm([
                'name' => 'Test Board',
                'url' => 'https://trello.com/b/test',
                'notes' => $longNotes,
            ])
            ->call('create')
            ->assertHasFormErrors(['notes']);
    }

    public function test_extra_information_stored_as_array(): void
    {
        $user = User::factory()->create(['is_admin' => true]);
        $this->actingAs($user);

        $boardData = [
            'name' => 'Test Board',
            'url' => 'https://trello.com/b/test-board',
            'extra_information' => [
                [
                    'title' => 'Test Title',
                    'value' => 'Test Value',
                ],
            ],
        ];

        Livewire::test('filament.resources.trello-board-resource.pages.create-trello-board')
            ->fillForm($boardData)
            ->call('create')
            ->assertNotified();

        $this->assertDatabaseHas('trello_boards', [
            'name' => 'Test Board',
            'extra_information' => json_encode($boardData['extra_information']),
        ]);
    }
}
