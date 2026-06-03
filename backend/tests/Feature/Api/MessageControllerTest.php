<?php

namespace Tests\Feature\Api;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_conversations(): void
    {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach($user->id);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/conversations');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_start_conversation(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($sender))
            ->postJson('/api/conversations', [
                'recipient_id' => $recipient->id,
                'subject' => 'Discussion',
                'content' => 'Hello!',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data']);
    }

    public function test_can_get_messages(): void
    {
        $user = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach($user->id);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/conversations/' . $conversation->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_send_message(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([$sender->id, $recipient->id]);

        $response = $this->withHeaders($this->authHeaders($sender))
            ->postJson('/api/conversations/' . $conversation->id . '/messages', [
                'content' => 'Test message',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data']);
    }
}
