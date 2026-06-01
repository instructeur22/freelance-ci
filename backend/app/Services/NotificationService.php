<?php
namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

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

    public function listForUser(User $user): LengthAwarePaginator
    {
        return $user->notifications()
            ->orderBy("created_at", "desc")
            ->paginate(20);
    }

    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);
        if (!$notification) return false;

        $notification->update(["read_at" => now()]);
        return true;
    }

    public function markAllAsRead(User $user): void
    {
        $user->notifications()->whereNull("read_at")->update([
            "read_at" => now(),
        ]);
    }

    public function delete(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);
        if (!$notification) return false;

        $notification->delete();
        return true;
    }
}
