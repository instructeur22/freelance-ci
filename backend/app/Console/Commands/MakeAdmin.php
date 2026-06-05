<?php
namespace App\Console\Commands;
use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
class MakeAdmin extends Command
{
    protected $signature = "make:admin {email : Email of the user to promote to admin}";
    protected $description = "Promote an existing user to administrator";

    public function handle(): int
    {
        $email = $this->argument("email");

        $user = User::where("email", $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return self::FAILURE;
        }

        if ($user->role === UserRole::Admin) {
            $this->warn("User {$email} is already an admin.");
            return self::SUCCESS;
        }

        $user->update([
            "role" => UserRole::Admin,
            "status" => AccountStatus::Active,
        ]);

        $this->info("User {$email} is now an admin.");

        return self::SUCCESS;
    }
}
