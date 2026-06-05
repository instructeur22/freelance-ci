<?php
namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class SupabaseJwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(["message" => "Token not provided"], 401);
        }

        try {
            JWT::$leeway = 120;
            $decoded = JWT::decode($token, $this->getKeys());
            $user = User::find($decoded->sub) ?? User::where("email", $decoded->email)->first();
            if (!$user) {
                return response()->json(["message" => "User not found"], 401);
            }
            Auth::setUser($user);
        } catch (\Throwable $e) {
            return response()->json(["message" => "Invalid token", "error" => $e->getMessage()], 401);
        }

        return $next($request);
    }

    private function getKeys(): array
    {
        $url = config("supabase.url") . "/auth/v1/.well-known/jwks.json";

        $jwks = Cache::store("file")->remember("supabase_jwks_v2", 3600, function () use ($url) {
            $http = config("app.env") === "local" ? Http::withoutVerifying() : Http::timeout(5);
            return $http->get($url)->json();
        });

        return JWK::parseKeySet($jwks);
    }
}
