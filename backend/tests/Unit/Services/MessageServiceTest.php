<?php

namespace Tests\Unit\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageServiceTest extends TestCase
{
    use RefreshDatabase;

    private MessageService $messageService;

    protected function setUp(): void
    {
        parent::setUp();
        $notificationService = new NotificationService();
        $this->messageService = new MessageService($notificationService);
    }

    public function test_startConversation_creates_conversation(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();

        $conversation = $this->messageService->startConversation($sender, [
            'recipient_id' => $recipient->id,
            'subject' => 'Project discussion',
            'content' => 'Hello, I am interested in your project.',
        ]);

        $this->assertNotNull($conversation);
        $this->assertInstanceOf(Conversation::class, $conversation);
        $this->assertEquals('Project discussion', $conversation->subject);
    }

    public function test_sendMessage_creates_message(): void
    {
        $sender = User::factory()->create();
        $recipient = User::factory()->create();
        $conversation = Conversation::factory()->create();

        $conversation->participants()->attach([$sender->id, $recipient->id]);

        $message = $this->messageService->sendMessage($sender, $conversation->id, [
            'content' => 'This is a test message',
        ]);

        $this->assertNotNull($message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('This is a test message', $message->content);
    }

    public function test_markAsRead_updates_message(): void
    {
        $user = User::factory()->create();
        $sender = User::factory()->create();
        $conversation = Conversation::factory()->create();
        $conversation->participants()->attach([$user->id, $sender->id]);
        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'status' => 'sent',
        ]);

        $result = $this->messageService->markAsRead($user, $message->id);

        $this->assertTrue($result);
        $this->assertNotNull($message->fresh()->read_at);
    }
}
