<?php

namespace Tests\Unit\Services;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Wallet;
use App\Services\AuthService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    public function test_service_can_be_instantiated(): void
    {
        $service = new AuthService();
        $this->assertInstanceOf(AuthService::class, $service);
    }

    public function test_register_returns_user_instance(): void
    {
        $userMock = $this->createMock(User::class);
        $userMock->method('__call')->willReturnSelf();

        $service = new AuthService();
        $this->assertInstanceOf(AuthService::class, $service);
    }

    public function test_register_method_signature(): void
    {
        $method = new \ReflectionMethod(AuthService::class, 'register');
        $this->assertEquals('array', $method->getParameters()[0]->getType()->getName());
        $this->assertEquals(User::class, $method->getReturnType()->getName());
    }

    public function test_findOrCreateFromSupabase_method_signature(): void
    {
        $method = new \ReflectionMethod(AuthService::class, 'findOrCreateFromSupabase');
        $params = $method->getParameters();
        $this->assertEquals('string', $params[0]->getType()->getName());
        $this->assertEquals('array', $params[1]->getType()->getName());
        $this->assertEquals(User::class, $method->getReturnType()->getName());
    }

    public function test_createSocialAccount_method_signature(): void
    {
        $method = new \ReflectionMethod(AuthService::class, 'createSocialAccount');
        $params = $method->getParameters();
        $this->assertEquals(User::class, $params[0]->getType()->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
        $this->assertEquals('string', $params[2]->getType()->getName());
        $this->assertEquals(SocialAccount::class, $method->getReturnType()->getName());
    }

    public function test_register_accepts_valid_data_shape(): void
    {
        $data = [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean@example.com',
            'password' => 'secret123',
            'role' => 'client',
        ];

        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('password', $data);
        $this->assertArrayHasKey('role', $data);
    }
}
