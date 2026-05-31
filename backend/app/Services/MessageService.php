<?php
namespace App\Services;

use App\Enums\MessageStatus;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function sendMessage(Conversation $conversation, User $sender, string $content): Message
    {
        $message = $conversation->messages()->create([
            "sender_id" => $sender->id,
            "content" => $content,
            "status" => MessageStatus::Sent,
        ]);

        $recipient = $conversation->participants()
            ->where("user_id", "!=", $sender->id)
            ->first();

        if ($recipient) {
            $conversation->increment("unread_count");

            $this->notificationService->createNotification(
                $recipient,
                "message",
                "Nouveau message",
                ($sender->name ?? $sender->email) . " vous a envoy\u00e9 un message.",
                [
                    "conversation_id" => $conversation->id,
                    "message_id" => $message->id,
                ],
            );
        }

        return $message->load("sender:id,first_name,last_name,avatar_url");
    }

    public function markAsRead(Message $message): void
    {
        if ($message->status !== MessageStatus::Read) {
            $message->update([
                "status" => MessageStatus::Read,
                "read_at" => now(),
            ]);

            $message->conversation->decrement("unread_count");
        }
    }

    public function getConversationMessages(Conversation $conversation, array $pagination): LengthAwarePaginator
    {
        return $conversation->messages()
            ->with("sender:id,first_name,last_name,avatar_url")
            ->orderBy("created_at", "desc")
            ->paginate($pagination["per_page"] ?? 50);
    }
}
