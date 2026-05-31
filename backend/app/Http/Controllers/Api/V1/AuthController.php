<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Auth', description: 'Authentification Supabase JWT')]
class AuthController extends ApiController
{
    public function __construct(
        protected AuthService $authService
    ) {}

    #[OA\Post(
        path: '/auth/register',
        summary: 'Register a new user',
        tags: ['Auth'],
    )]
    #[OA\Response(response: 201, description: 'Registration successful')]
    #[OA\Response(response: 400, description: 'Registration failed')]
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        if (!$result) {
            return $this->error('Registration failed', 400);
        }

        return $this->created($result, 'Registration successful');
    }

    #[OA\Post(
        path: '/auth/login',
        summary: 'Login with Supabase JWT',
        tags: ['Auth'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Login successful')]
    #[OA\Response(response: 401, description: 'Invalid credentials')]
    public function login(Request $request): JsonResponse
    {
        $result = $this->authService->validateSupabaseToken($request);

        if (!$result) {
            return $this->error('Invalid credentials', 401);
        }

        return $this->success($result, 'Login successful');
    }

    #[OA\Post(
        path: '/auth/social/{provider}',
        summary: 'Authenticate with social provider',
        tags: ['Auth'],
    )]
    #[OA\Parameter(name: 'provider', in: 'path', required: true, description: 'Social provider (google, github, etc.)')]
    #[OA\Response(response: 200, description: 'Social authentication successful')]
    #[OA\Response(response: 400, description: 'Social authentication failed')]
    public function socialAuth(string $provider, Request $request): JsonResponse
    {
        $result = $this->authService->handleSocialAuth($provider, $request);

        if (!$result) {
            return $this->error('Social authentication failed', 400);
        }

        return $this->success($result, 'Social authentication successful');
    }

    #[OA\Get(
        path: '/auth/me',
        summary: 'Get current authenticated user',
        tags: ['Auth'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Current user data')]
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('profile');

        return $this->success($user);
    }
}
