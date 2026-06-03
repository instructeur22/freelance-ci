<?php

namespace Tests\Unit\Services;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use App\Services\ContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractServiceTest extends TestCase
{
    use RefreshDatabase;

    private ContractService $contractService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contractService = new ContractService();
    }

    public function test_can_instantiate(): void
    {
        $this->assertNotNull($this->contractService);
        $this->assertInstanceOf(ContractService::class, $this->contractService);
    }

    public function test_createContract_creates_contract_from_data(): void
    {
        $client = User::factory()->client()->create();
        $freelance = User::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create(['project_id' => $project->id, 'freelance_id' => $freelance->id]);

        $data = [
            'project_id' => $project->id,
            'quote_id' => $quote->id,
            'client_id' => $client->id,
            'freelance_id' => $freelance->id,
            'title' => 'Contract title',
            'description' => 'Contract description',
            'total_amount' => 100000,
            'currency' => 'XOF',
            'status' => ContractStatus::Draft,
        ];

        $contract = $this->contractService->createContract($data);
        $this->assertTrue($contract instanceof Contract, 'Expected Contract, got: ' . get_debug_type($contract));
        $this->assertEquals(100000, $contract->total_amount);
        $this->assertEquals(ContractStatus::Draft, $contract->status);
    }

    public function test_sign_contract_sets_signed_at(): void
    {
        $client = User::factory()->client()->create();
        $freelance = User::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create(['project_id' => $project->id, 'freelance_id' => $freelance->id]);
        $contract = Contract::factory()->create([
            'client_id' => $client->id,
            'freelance_id' => $freelance->id,
            'project_id' => $project->id,
            'quote_id' => $quote->id,
        ]);

        $result = $this->contractService->sign($client, $contract->id);

        $this->assertNotNull($result);
        $this->assertNotNull($result->client_signed_at);
    }

    public function test_sign_contract_activates_when_both_signed(): void
    {
        $client = User::factory()->client()->create();
        $freelance = User::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create(['project_id' => $project->id, 'freelance_id' => $freelance->id]);
        $contract = Contract::factory()->create([
            'client_id' => $client->id,
            'freelance_id' => $freelance->id,
            'project_id' => $project->id,
            'quote_id' => $quote->id,
            'freelance_signed_at' => now(),
        ]);

        $result = $this->contractService->sign($client, $contract->id);

        $this->assertNotNull($result);
        $this->assertEquals(ContractStatus::Signed, $result->status);
    }

    public function test_addMilestone_adds_milestone_to_contract(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create(['project_id' => $project->id]);
        $contract = Contract::factory()->active()->create([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'quote_id' => $quote->id,
        ]);

        $milestone = $this->contractService->addMilestone($client, $contract->id, [
            'title' => 'First milestone',
            'description' => 'Do the first part',
            'amount' => 50000,
            'due_date' => now()->addDays(30),
            'sort_order' => 1,
        ]);

        $this->assertInstanceOf(Milestone::class, $milestone);
        $this->assertEquals($contract->id, $milestone->contract_id);
        $this->assertEquals(50000, $milestone->amount);
    }

    public function test_deliverMilestone_marks_milestone_as_delivered(): void
    {
        $freelance = User::factory()->create();
        $project = Project::factory()->create();
        $quote = Quote::factory()->create(['project_id' => $project->id, 'freelance_id' => $freelance->id]);
        $contract = Contract::factory()->active()->create([
            'freelance_id' => $freelance->id,
            'project_id' => $project->id,
            'quote_id' => $quote->id,
        ]);
        $milestone = Milestone::factory()->create([
            'contract_id' => $contract->id,
            'is_completed' => false,
        ]);

        $result = $this->contractService->deliverMilestone($freelance, $contract->id, $milestone->id);

        $this->assertNotNull($result);
        $this->assertTrue($result->is_completed);
        $this->assertNotNull($result->delivered_at);
    }

    public function test_validateMilestone_validates_delivered_milestone(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create(['project_id' => $project->id]);
        $contract = Contract::factory()->active()->create([
            'client_id' => $client->id,
            'project_id' => $project->id,
            'quote_id' => $quote->id,
        ]);
        $milestone = Milestone::factory()->delivered()->create([
            'contract_id' => $contract->id,
        ]);

        $result = $this->contractService->validateMilestone($client, $contract->id, $milestone->id);

        $this->assertNotNull($result);
        $this->assertNotNull($result->validated_at);
    }
}
