<?php

namespace Tests\Feature\Api;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_notifications(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->putJson('/api/notifications/' . $notification->id . '/read');

        $response->assertStatus(200);
    }

    public function test_can_mark_all_as_read(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->putJson('/api/notifications/read-all');

        $response->assertStatus(200);
    }

    public function test_can_delete_notification(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->deleteJson('/api/notifications/' . $notification->id);

        $response->assertStatus(200);
    }
}
