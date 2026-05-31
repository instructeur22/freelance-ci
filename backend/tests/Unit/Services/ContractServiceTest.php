<?php

namespace Tests\Unit\Services;

use App\Models\Contract;
use App\Services\ContractService;
use Tests\TestCase;

class ContractServiceTest extends TestCase
{
    private ContractService $contractService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contractService = new ContractService();
    }

    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(ContractService::class, $this->contractService);
    }
}
