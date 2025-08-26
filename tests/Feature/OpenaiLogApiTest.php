<?php

namespace Tests\Feature;

use App\Models\OpenaiLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenaiLogApiTest extends TestCase
{
  use RefreshDatabase;

  public function test_guest_users_are_unauthorized_to_access_openai_logs()
  {
    $response = $this->getJson('/api/openai-logs');
    $response->assertStatus(401);
  }

  public function test_authenticated_user_can_get_their_openai_logs()
  {
    $user = User::factory()->create();
    $other = User::factory()->create();

    OpenaiLog::create([
      'user_id' => $user->id,
      'conversation_id' => 'conv-1',
      'model' => 'gpt-3.5-turbo',
      'endpoint' => '/v1/chat/completions',
      'request_payload' => json_encode(['messages' => [['role' => 'user', 'content' => 'hello']]]),
      'response_text' => json_encode(['choices' => [['message' => ['role' => 'assistant', 'content' => 'hi']]]]),
      'status_code' => 200,
      'duration_ms' => 123,
    ]);

    OpenaiLog::create([
      'user_id' => $user->id,
      'conversation_id' => 'conv-2',
      'model' => 'gpt-3.5-turbo',
      'endpoint' => '/v1/chat/completions',
      'request_payload' => json_encode(['messages' => [['role' => 'user', 'content' => 'hi']]]),
      'response_text' => json_encode(['choices' => [['message' => ['role' => 'assistant', 'content' => 'hello']]]]),
      'status_code' => 200,
      'duration_ms' => 50,
    ]);

    // log for another user
    OpenaiLog::create([
      'user_id' => $other->id,
      'conversation_id' => 'conv-3',
      'model' => 'gpt-3.5-turbo',
      'endpoint' => '/v1/chat/completions',
      'request_payload' => json_encode(['messages' => [['role' => 'user', 'content' => 'hey']]]),
      'response_text' => json_encode(['choices' => [['message' => ['role' => 'assistant', 'content' => 'yo']]]]),
      'status_code' => 200,
      'duration_ms' => 10,
    ]);

    $this->actingAs($user, 'sanctum')
      ->getJson('/api/openai-logs')
      ->assertStatus(200)
      ->assertJsonCount(2, 'logs')
      ->assertJsonPath('logs.0.conversation_id', 'conv-1')
      ->assertJsonPath('logs.1.conversation_id', 'conv-2');
  }

  public function test_limit_query_parameter_limits_results()
  {
    $user = User::factory()->create();

    for ($i = 1; $i <= 3; $i++) {
      OpenaiLog::create([
        'user_id' => $user->id,
        'conversation_id' => 'conv-' . $i,
        'model' => 'gpt-3.5-turbo',
        'endpoint' => '/v1/chat/completions',
        'request_payload' => json_encode(['messages' => [['role' => 'user', 'content' => 'msg']]]),
        'response_text' => json_encode(['choices' => [['message' => ['role' => 'assistant', 'content' => 'r']]]]),
        'status_code' => 200,
        'duration_ms' => 5,
      ]);
    }

    $this->actingAs($user, 'sanctum')
      ->getJson('/api/openai-logs?limit=1')
      ->assertStatus(200)
      ->assertJsonCount(1, 'logs');
  }

  public function test_conversation_filter_limits_results()
  {
    $user = User::factory()->create();

    OpenaiLog::create([
      'user_id' => $user->id,
      'conversation_id' => 'target',
      'model' => 'gpt-3.5-turbo',
      'endpoint' => '/v1/chat/completions',
      'request_payload' => json_encode(['messages' => []]),
      'response_text' => json_encode(['choices' => [['message' => ['role' => 'assistant', 'content' => 'a']]]]),
      'status_code' => 200,
      'duration_ms' => 1,
    ]);
    OpenaiLog::create([
      'user_id' => $user->id,
      'conversation_id' => 'other',
      'model' => 'gpt-3.5-turbo',
      'endpoint' => '/v1/chat/completions',
      'request_payload' => json_encode(['messages' => []]),
      'response_text' => json_encode(['choices' => [['message' => ['role' => 'assistant', 'content' => 'b']]]]),
      'status_code' => 200,
      'duration_ms' => 1,
    ]);
    $this->actingAs($user, 'sanctum')
      ->getJson('/api/openai-logs?conversation_id=target')
      ->assertStatus(200)
      ->assertJsonCount(1, 'logs');
  }
}
