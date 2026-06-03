<?php

namespace Tests\Unit\Services;

use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private NotificationService $notificationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = new NotificationService();
    }

    public function test_createNotification_creates_notification(): void
    {
        $user = User::factory()->create();

        $notification = $this->notificationService->createNotification(
            $user,
            'system',
            'Welcome!',
            'Welcome to the platform',
            ['key' => 'value'],
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals($user->id, $notification->user_id);
        $this->assertEquals('Welcome!', $notification->title);
    }

    public function test_markAsRead_sets_read_at(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        $result = $this->notificationService->markAsRead($user, $notification->id);

        $this->assertTrue($result);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_markAllAsRead_marks_all(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        $this->notificationService->markAllAsRead($user);

        $this->assertEquals(0, Notification::where('user_id', $user->id)->whereNull('read_at')->count());
    }

    public function test_delete_removes_notification(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->create(['user_id' => $user->id]);

        $result = $this->notificationService->delete($user, $notification->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }
}
