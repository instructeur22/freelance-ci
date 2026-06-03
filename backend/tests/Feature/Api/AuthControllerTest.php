<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'freelance',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'email', 'first_name', 'last_name']]);
    }

    public function test_register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422);
    }

    public function test_can_login(): void
    {
        $user = User::factory()->create();
        Wallet::factory()->create(['user_id' => $user->id]);

        $token = $this->generateJwt($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/auth/login');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'email']]);
    }

    public function test_can_get_me(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $user->id]]);
    }
}
