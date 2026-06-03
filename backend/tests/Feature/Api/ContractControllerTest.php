<?php

namespace Tests\Feature\Api;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_contracts(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();
        $quote = Quote::factory()->create(['project_id' => $project->id]);
        Contract::factory()->count(2)->create([
            'client_id' => $user->id,
            'project_id' => $project->id,
            'quote_id' => $quote->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($user))
            ->getJson('/api/contracts');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_show_contract(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create(['project_id' => $project->id]);
        $contract = Contract::factory()->create([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'quote_id' => $quote->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($client))
            ->getJson('/api/contracts/' . $contract->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_sign_contract(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create(['project_id' => $project->id]);
        $contract = Contract::factory()->create([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'quote_id' => $quote->id,
            'status' => ContractStatus::Draft,
        ]);

        $response = $this->withHeaders($this->authHeaders($client))
            ->postJson('/api/contracts/' . $contract->id . '/sign');

        $response->assertStatus(200);
    }

    public function test_can_add_milestone(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create(['project_id' => $project->id]);
        $contract = Contract::factory()->active()->create([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'quote_id' => $quote->id,
        ]);

        $response = $this->withHeaders($this->authHeaders($client))
            ->postJson('/api/contracts/' . $contract->id . '/milestones', [
                'title' => 'Milestone 1',
                'description' => 'First deliverable',
                'amount' => 50000,
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'sort_order' => 1,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data']);
    }

    public function test_can_deliver_milestone(): void
    {
        $freelance = User::factory()->create();
        $project = Project::factory()->create();
        $quote = Quote::factory()->create(['project_id' => $project->id, 'freelance_id' => $freelance->id]);
        $contract = Contract::factory()->active()->create([
            'freelance_id' => $freelance->id,
            'project_id' => $project->id,
            'quote_id' => $quote->id,
        ]);
        $milestone = Milestone::factory()->create(['contract_id' => $contract->id]);

        $response = $this->withHeaders($this->authHeaders($freelance))
            ->postJson('/api/contracts/' . $contract->id . '/milestones/' . $milestone->id . '/deliver');

        $response->assertStatus(200);
    }
}
