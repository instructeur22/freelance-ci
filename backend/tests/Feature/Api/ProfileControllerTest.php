<?php

namespace Tests\Feature\Api;

use App\Models\ClientProfile;
use App\Models\FreelanceProfile;
use App\Models\PortfolioItem;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_freelances(): void
    {
        $freelance = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $freelance->id]);

        $response = $this->getJson('/api/freelances');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_get_freelance_detail(): void
    {
        $freelance = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $freelance->id]);

        $response = $this->getJson('/api/freelances/' . $freelance->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_get_my_profile(): void
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/profiles/me');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_update_my_profile(): void
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->putJson('/api/profiles/me', [
                'bio' => 'Updated bio',
                'city' => 'Abidjan',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'bio' => 'Updated bio',
        ]);
    }

    public function test_can_get_freelance_profile(): void
    {
        $user = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/profiles/freelance');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_update_freelance_profile(): void
    {
        $user = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->putJson('/api/profiles/freelance', [
                'professional_title' => 'Expert Laravel',
                'is_available' => true,
            ]);

        $response->assertStatus(200);
    }

    public function test_can_add_skill(): void
    {
        $user = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $user->id]);
        $skill = Skill::factory()->create();

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/profiles/freelance/skills', [
                'skill_id' => $skill->id,
                'proficiency_level' => 'avance',
            ]);

        $response->assertStatus(200);
    }

    public function test_can_add_portfolio_item(): void
    {
        $user = User::factory()->create();
        FreelanceProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->postJson('/api/profiles/freelance/portfolio', [
                'title' => 'Mon projet',
                'description' => 'Description du projet',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data']);
    }

    public function test_can_get_client_profile(): void
    {
        $user = User::factory()->client()->create();
        ClientProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/profiles/client');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
