<?php
namespace App\Console\Commands;
use App\Services\GeniusPayService;
use Illuminate\Console\Command;
class SyncGeniusPayTransactions extends Command
{
    protected $signature = "genius-pay:sync";
    protected $description = "Sync pending Genius Pay transactions";
    public function handle(GeniusPayService $geniusPay): void
    {
        $this->info("Syncing Genius Pay transactions...");
        $result = $geniusPay->syncTransactions();
        $this->info("Checked: {$result["checked"]}, Updated: {$result["updated"]}, Failed: {$result["failed"]}");
    }
}
