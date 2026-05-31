<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\WalletService;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    public function test_service_can_be_instantiated(): void
    {
        $service = new WalletService();
        $this->assertInstanceOf(WalletService::class, $service);
    }
}
