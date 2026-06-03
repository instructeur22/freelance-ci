<?php

namespace Tests\Feature\Api;

use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_quotes(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        Quote::factory()->count(2)->create(['project_id' => $project->id]);

        $response = $this->withHeaders($this->authHeaders($client))
            ->getJson('/api/projects/' . $project->id . '/quotes');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_create_quote(): void
    {
        $freelance = User::factory()->create();
        $project = Project::factory()->create();

        $response = $this->withHeaders($this->authHeaders($freelance))
            ->postJson('/api/projects/' . $project->id . '/quotes', [
                'amount' => 75000,
                'currency' => 'XOF',
                'estimated_duration' => 14,
                'proposal' => 'I can do this job',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data']);
    }

    public function test_can_accept_quote(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create(['project_id' => $project->id]);

        $response = $this->withHeaders($this->authHeaders($client))
            ->postJson('/api/quotes/' . $quote->id . '/accept');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_refuse_quote(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create(['project_id' => $project->id]);

        $response = $this->withHeaders($this->authHeaders($client))
            ->postJson('/api/quotes/' . $quote->id . '/refuse');

        $response->assertStatus(200);
    }
}
