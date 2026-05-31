<?php
namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                "first_name" => $data["first_name"] ?? $data["name"] ?? null,
                "last_name" => $data["last_name"] ?? null,
                "email" => $data["email"],
                "password" => isset($data["password"]) ? Hash::make($data["password"]) : null,
                "role" => $data["role"] ?? UserRole::Client,
                "status" => AccountStatus::Active,
            ]);

            $user->profile()->create([]);

            if ($user->role === UserRole::Freelance) {
                $user->freelanceProfile()->create([]);
            } elseif ($user->role === UserRole::Client) {
                $user->clientProfile()->create([]);
            }

            $user->wallet()->create(["balance" => 0]);

            return $user;
        });
    }

    public function findOrCreateFromSupabase(string $supabaseId, array $data): User
    {
        $user = User::where("id", $supabaseId)->first();

        if ($user) {
            $user->update(["last_login_at" => now()]);
            return $user;
        }

        return DB::transaction(function () use ($supabaseId, $data) {
            $user = User::where("email", $data["email"])->first();

            if ($user) {
                $user->update([
                    "last_login_at" => now(),
                ]);
                return $user;
            }

            return $this->register([
                "first_name" => $data["first_name"] ?? explode("@", $data["email"])[0],
                "email" => $data["email"],
                "role" => UserRole::Client,
            ]);
        });
    }

    public function createSocialAccount(User $user, string $provider, string $providerId): SocialAccount
    {
        return $user->socialAccounts()->create([
            "provider" => $provider,
            "provider_id" => $providerId,
        ]);
    }
}
