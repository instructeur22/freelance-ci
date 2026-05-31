<?php
namespace App\Console\Commands;
use App\Services\EscrowService;
use Illuminate\Console\Command;
class ReleaseEscrowFunds extends Command
{
    protected $signature = "escrow:release-auto";
    protected $description = "Auto-release escrow funds after delay period";
    public function handle(EscrowService $escrow): void
    {
        $this->info("Releasing auto-escrow funds...");
        $count = $escrow->autoReleaseSchedule();
        $this->info("Released {$count} escrows");
    }
}
