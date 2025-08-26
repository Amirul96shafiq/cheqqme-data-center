<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientApiTest extends TestCase
{
  use RefreshDatabase;

  public function test_guest_users_are_unauthorized_to_access_clients()
  {
    $response = $this->getJson('/api/clients');
    $response->assertStatus(401);
  }

  public function test_authenticated_user_can_get_all_clients()
  {
    $user = User::factory()->create();
    Client::factory()->count(2)->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/clients');
    $response->assertStatus(200)
      ->assertJsonCount(2, 'clients');
  }
}
