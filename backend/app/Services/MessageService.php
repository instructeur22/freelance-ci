<?php
namespace App\Services;

use App\Enums\MessageStatus;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class MessageService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    public function listConversations(User $user): LengthAwarePaginator
    {
        return $user->conversations()
            ->with(["participants:id,first_name,last_name,avatar_url"])
            ->orderBy("last_message_at", "desc")
            ->paginate(20);
    }

    public function startConversation(User $user, array $data): ?Conversation
    {
        $recipientId = $data["recipient_id"] ?? $data["user_id"] ?? null;
        if (!$recipientId) return null;

        $projectId = $data["project_id"] ?? null;

        $existing = Conversation::whereHas("participants", function (Builder $q) use ($user, $recipientId) {
            $q->whereIn("user_id", [$user->id, $recipientId]);
        })->when($projectId, function (Builder $q) use ($projectId) {
            $q->where("project_id", $projectId);
        })->first();

        if ($existing) return $existing;

        $conversation = Conversation::create([
            "project_id" => $projectId,
            "contract_id" => $data["contract_id"] ?? null,
            "subject" => $data["subject"] ?? null,
        ]);

        $conversation->participants()->attach([$user->id, $recipientId]);

        return $conversation;
    }

    public function getMessages(User $user, string $conversationId): ?LengthAwarePaginator
    {
        $conversation = $user->conversations()->find($conversationId);
        if (!$conversation) return null;

        return $conversation->messages()
            ->with("sender:id,first_name,last_name,avatar_url")
            ->orderBy("created_at", "desc")
            ->paginate(50);
    }

    public function sendMessage(User $user, string $conversationId, array $data): ?Message
    {
        $conversation = $user->conversations()->find($conversationId);
        if (!$conversation) return null;

        $message = $conversation->messages()->create([
            "sender_id" => $user->id,
            "content" => $data["content"],
            "status" => MessageStatus::Sent,
        ]);

        $conversation->touch("last_message_at");

        $recipients = $conversation->participants()
            ->where("user_id", "!=", $user->id)
            ->get();

        foreach ($recipients as $recipient) {
            $this->notificationService->createNotification(
                $recipient,
                "message",
                "Nouveau message",
                ($user->name ?? $user->email) . " vous a envoy\u00e9 un message.",
                [
                    "conversation_id" => $conversation->id,
                    "message_id" => $message->id,
                ],
            );
        }

        return $message->load("sender:id,first_name,last_name,avatar_url");
    }

    public function markAsRead(User $user, string $messageId): bool
    {
        $message = Message::where("conversation_id", function ($q) use ($user) {
            $q->select("conversation_id")
              ->from("conversation_participants")
              ->where("user_id", $user->id)
              ->limit(1);
        })->find($messageId);

        if (!$message) return false;

        if ($message->status !== MessageStatus::Read) {
            $message->update([
                "status" => MessageStatus::Read,
                "read_at" => now(),
            ]);
        }

        return true;
    }
}
