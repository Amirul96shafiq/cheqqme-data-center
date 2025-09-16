<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotInitialMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_initial_messages_only_sent_once()
    {
        // Create a test user
        $user = User::factory()->create();

        // Act as the user
        $this->actingAs($user);

        // Test that initial conversation returns empty (no duplicate messages)
        $response = $this->get('/chatbot/conversation?conversation_id=test_conv_123');

        $response->assertStatus(200);
        $data = $response->json();

        // Should return empty conversation for new conversation ID
        $this->assertArrayHasKey('conversation', $data);
        $this->assertEmpty($data['conversation']);
    }

    public function test_chatbot_handles_empty_conversation_correctly()
    {
        // Create a test user
        $user = User::factory()->create();

        // Act as the user
        $this->actingAs($user);

        // Test multiple requests with same conversation ID
        $conversationId = 'test_conv_'.time();

        // First request
        $response1 = $this->get("/chatbot/conversation?conversation_id={$conversationId}");
        $response1->assertStatus(200);
        $data1 = $response1->json();
        $this->assertEmpty($data1['conversation']);

        // Second request with same conversation ID
        $response2 = $this->get("/chatbot/conversation?conversation_id={$conversationId}");
        $response2->assertStatus(200);
        $data2 = $response2->json();
        $this->assertEmpty($data2['conversation']);

        // Both should return empty conversation
        $this->assertEquals($data1, $data2);
    }

    public function test_chatbot_conversation_endpoint_exists()
    {
        // Create a test user
        $user = User::factory()->create();

        // Act as the user
        $this->actingAs($user);

        // Test that the endpoint exists and is accessible
        $response = $this->get('/chatbot/conversation?conversation_id=test');

        $response->assertStatus(200);
        $this->assertArrayHasKey('conversation', $response->json());
    }

    public function test_chatbot_clear_conversation_works()
    {
        // Create a test user
        $user = User::factory()->create();

        // Act as the user
        $this->actingAs($user);

        // Test clearing a conversation
        $response = $this->post('/chatbot/clear', [
            'conversation_id' => 'test_conv_123',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // Should return a new conversation ID
        $this->assertArrayHasKey('conversation_id', $data);
        $this->assertNotEquals('test_conv_123', $data['conversation_id']);
    }
}
