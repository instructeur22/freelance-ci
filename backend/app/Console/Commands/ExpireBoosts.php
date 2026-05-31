<?php
namespace App\Console\Commands;
use App\Models\Boost;
use Illuminate\Console\Command;
class ExpireBoosts extends Command
{
    protected $signature = "boosts:expire";
    protected $description = "Expire boosts that have ended";
    public function handle(): void
    {
        $count = Boost::where("is_active", true)->where("ends_at", "<", now())->update(["is_active" => false]);
        $this->info("Expired {$count} boosts");
    }
}
