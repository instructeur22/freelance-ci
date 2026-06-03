<?php

namespace Tests;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config([
            'supabase.jwt_secret' => 'this-is-a-secret-key-that-is-at-least-32-characters-long',
            'services.supabase.jwt_secret' => 'this-is-a-secret-key-that-is-at-least-32-characters-long',
        ]);
    }

    protected function generateJwt(User $user): string
    {
        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + 3600,
        ];

        return JWT::encode($payload, config('supabase.jwt_secret'), 'HS256');
    }

    protected function authHeaders(User $user): array
    {
        return ['Authorization' => 'Bearer ' . $this->generateJwt($user)];
    }
}
