<?php
namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Wallet;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

            app(ReferralService::class)->getOrCreateCode($user);

            if (!empty($data["referral_code"])) {
                app(ReferralService::class)->trackReferral($user, $data["referral_code"]);
            }

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

    public function validateSupabaseToken(Request $request): ?User
    {
        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }

        try {
            $secret = config("services.supabase.jwt_secret");
            $decoded = JWT::decode($token, new Key($secret, "HS256"));

            $supabaseId = $decoded->sub ?? null;
            $email = $decoded->email ?? null;

            if (!$supabaseId) {
                return null;
            }

            return $this->findOrCreateFromSupabase($supabaseId, [
                "email" => $email ?? "user-$supabaseId@placeholder.local",
                "first_name" => $decoded->user_metadata?->full_name
                    ?? $decoded->user_metadata?->name
                    ?? $decoded->raw_user_meta_data?->full_name
                    ?? explode("@", $email ?? "")[0]
                    ?? "User",
            ]);
        } catch (\Exception $e) {
            Log::error("Supabase JWT validation failed", ["error" => $e->getMessage()]);
            return null;
        }
    }

    public function handleSocialAuth(string $provider, Request $request): ?User
    {
        $token = $request->input("token") ?? $request->bearerToken();
        if (!$token) {
            return null;
        }

        try {
            if ($provider === "google" || $provider === "github") {
                $response = Http::withToken($token)
                    ->get("https://www.googleapis.com/oauth2/v3/userinfo");

                if ($response->successful()) {
                    $data = $response->json();
                    $providerId = $data["sub"];

                    $social = SocialAccount::where("provider", $provider)
                        ->where("provider_id", $providerId)
                        ->first();

                    if ($social) {
                        $social->user->update(["last_login_at" => now()]);
                        return $social->user;
                    }

                    return DB::transaction(function () use ($data, $provider, $providerId) {
                        $user = User::where("email", $data["email"])->first();

                        if (!$user) {
                            $user = $this->register([
                                "first_name" => $data["given_name"] ?? explode("@", $data["email"])[0],
                                "last_name" => $data["family_name"] ?? null,
                                "email" => $data["email"],
                            ]);
                        }

                        $this->createSocialAccount($user, $provider, $providerId);
                        $user->update(["last_login_at" => now()]);

                        return $user;
                    });
                }
            }

            $supabaseToken = $request->input("supabase_token") ?? $token;
            return $this->validateSupabaseToken(
                Request::create("", "GET", [], [], [], ["HTTP_AUTHORIZATION" => "Bearer $supabaseToken"])
            );
        } catch (\Exception $e) {
            Log::error("Social auth failed for $provider", ["error" => $e->getMessage()]);
            return null;
        }
    }

    public function createSocialAccount(User $user, string $provider, string $providerId): SocialAccount
    {
        return $user->socialAccounts()->create([
            "provider" => $provider,
            "provider_id" => $providerId,
        ]);
    }
}
