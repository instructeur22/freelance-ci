<?php

namespace Tests\Feature\Api;

use App\Models\FreelanceProfile;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_freelances(): void
    {
        $user = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->getJson('/api/freelances');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_list_projects(): void
    {
        Project::factory()->create();

        $response = $this->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
