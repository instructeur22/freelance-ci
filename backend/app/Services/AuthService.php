<?php
namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Wallet;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                "first_name" => $data["first_name"] ?? $data["name"] ?? null,
                "last_name" => $data["last_name"] ?? null,
                "email" => $data["email"],
                "password" => isset($data["password"]) ? Hash::make($data["password"]) : Hash::make(Str::random(40)),
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
            JWT::$leeway = 120;
            $decoded = JWT::decode($token, $this->getJwksKeys());

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
        } catch (\Throwable $e) {
            Log::error("Supabase JWT validation failed", [
                "error" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);
            return null;
        }
    }

    private function getJwksKeys(): array
    {
        $url = config('services.supabase.url') . '/auth/v1/.well-known/jwks.json';

        $jwks = Cache::store('file')->remember('supabase_jwks_keys', 3600, function () use ($url) {
            $http = config('app.env') === 'local' ? Http::withoutVerifying() : Http::timeout(5);
            return $http->get($url)->json();
        });

        return JWK::parseKeySet($jwks);
    }

    public function handleSocialAuth(string $provider, Request $request): ?User
    {
        $token = $request->input("token") ?? $request->bearerToken();
        if (!$token) {
            return null;
        }

        $supabaseToken = $request->input("supabase_token") ?? $token;

        if ($provider === "google" || $provider === "github") {
            try {
                $http = config('app.env') === 'local' ? Http::withoutVerifying() : Http::timeout(5);
                $response = $http->withToken($token)
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
            } catch (\Exception $e) {
                Log::warning("Provider API call failed, falling back to Supabase JWT", [
                    "provider" => $provider,
                    "error" => $e->getMessage(),
                ]);
            }
        }

        try {
            return $this->validateSupabaseToken(
                Request::create("", "GET", [], [], [], ["HTTP_AUTHORIZATION" => "Bearer $supabaseToken"])
            );
        } catch (\Exception $e) {
            Log::error("Supabase JWT validation failed for $provider", ["error" => $e->getMessage()]);
            return null;
        }
    }

    public function completeOnboarding(User $user, string $role): User
    {
        return DB::transaction(function () use ($user, $role) {
            $user->update(['role' => $role]);

            if (!$user->profile) {
                $user->profile()->create([]);
            }

            if ($role === UserRole::Freelance->value && !$user->freelanceProfile) {
                $user->freelanceProfile()->create([]);
            } elseif ($role === UserRole::Client->value && !$user->clientProfile) {
                $user->clientProfile()->create([]);
            }

            if (!$user->wallet) {
                $user->wallet()->create(['balance' => 0]);
            }

            return $user->fresh()->load('profile');
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
