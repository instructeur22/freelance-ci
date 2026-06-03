<?php

namespace Tests\Unit\Services;

use App\Enums\ContractStatus;
use App\Enums\QuoteStatus;
use App\Models\Contract;
use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use App\Services\ContractService;
use App\Services\NotificationService;
use App\Services\QuoteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteServiceTest extends TestCase
{
    use RefreshDatabase;

    private QuoteService $quoteService;

    protected function setUp(): void
    {
        parent::setUp();
        $contractService = new ContractService();
        $notificationService = new NotificationService();
        $this->quoteService = new QuoteService($contractService, $notificationService);
    }

    public function test_create_creates_quote(): void
    {
        $freelance = User::factory()->create();
        $project = Project::factory()->create();

        $quote = $this->quoteService->create($freelance, $project->id, [
            'amount' => 75000,
            'currency' => 'XOF',
            'estimated_duration' => 14,
            'proposal' => 'I can do this job',
        ]);

        $this->assertInstanceOf(Quote::class, $quote);
        $this->assertEquals($freelance->id, $quote->freelance_id);
        $this->assertEquals(QuoteStatus::Pending, $quote->status);
    }

    public function test_accept_creates_contract(): void
    {
        $client = User::factory()->client()->create();
        $freelance = User::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create([
            'project_id' => $project->id,
            'freelance_id' => $freelance->id,
            'status' => QuoteStatus::Pending,
        ]);

        $contract = $this->quoteService->accept($client, $quote->id);

        $this->assertNotNull($contract);
        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals(ContractStatus::Draft, $contract->status);
        $this->assertEquals(QuoteStatus::Accepted, $quote->fresh()->status);
    }

    public function test_refuse_rejects_quote(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);
        $quote = Quote::factory()->create([
            'project_id' => $project->id,
            'status' => QuoteStatus::Pending,
        ]);

        $result = $this->quoteService->refuse($client, $quote->id);

        $this->assertTrue($result);
        $this->assertEquals(QuoteStatus::Refused, $quote->fresh()->status);
    }
}
