<?php
namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function createNotification(User $user, string $type, string $title, ?string $body = null, ?array $data = null): Notification
    {
        return $user->notifications()->create([
            "type" => $type,
            "title" => $title,
            "body" => $body,
            "data" => $data,
        ]);
    }

    public function markAsRead(string $notificationId): void
    {
        Notification::where("id", $notificationId)->update([
            "read_at" => now(),
        ]);
    }

    public function markAllAsRead(User $user): void
    {
        $user->notifications()->whereNull("read_at")->update([
            "read_at" => now(),
        ]);
    }
}
