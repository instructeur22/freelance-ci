<?php
namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $secret = config("supabase.jwt_secret");
            $decoded = JWT::decode($token, new Key($secret, "HS256"));
            $user = User::find($decoded->sub) ?? User::where("email", $decoded->email)->first();
            if (!$user) {
                return response()->json(["message" => "User not found"], 401);
            }
            Auth::setUser($user);
        } catch (\Exception $e) {
            return response()->json(["message" => "Invalid token", "error" => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
