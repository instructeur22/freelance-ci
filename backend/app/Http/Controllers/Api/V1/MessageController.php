<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Messaging', description: 'Messagerie et conversations')]
class MessageController extends ApiController
{
    public function __construct(
        protected MessageService $messageService
    ) {}

    #[OA\Get(
        path: '/conversations',
        summary: 'List conversations',
        tags: ['Messaging'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of conversations')]
    public function conversations(Request $request): JsonResponse
    {
        $conversations = $this->messageService->listConversations($request->user());

        return $this->success($conversations);
    }

    #[OA\Post(
        path: '/conversations',
        summary: 'Start a new conversation',
        tags: ['Messaging'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 201, description: 'Conversation started')]
    #[OA\Response(response: 400, description: 'Unable to start conversation')]
    public function startConversation(Request $request): JsonResponse
    {
        $conversation = $this->messageService->startConversation($request->user(), $request->all());

        if (!$conversation) {
            return $this->error('Unable to start conversation', 400);
        }

        return $this->created($conversation, 'Conversation started');
    }

    #[OA\Get(
        path: '/conversations/{id}',
        summary: 'Get messages in a conversation',
        tags: ['Messaging'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Conversation ID')]
    #[OA\Response(response: 200, description: 'List of messages')]
    #[OA\Response(response: 404, description: 'Conversation not found')]
    public function messages(string $id, Request $request): JsonResponse
    {
        $messages = $this->messageService->getMessages($request->user(), $id);

        if ($messages === null) {
            return $this->error('Conversation not found', 404);
        }

        return $this->success($messages);
    }

    #[OA\Post(
        path: '/conversations/{id}/messages',
        summary: 'Send a message in a conversation',
        tags: ['Messaging'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Conversation ID')]
    #[OA\Response(response: 201, description: 'Message sent')]
    #[OA\Response(response: 400, description: 'Unable to send message')]
    public function sendMessage(string $id, Request $request): JsonResponse
    {
        $message = $this->messageService->sendMessage($request->user(), $id, $request->all());

        if (!$message) {
            return $this->error('Unable to send message', 400);
        }

        return $this->created($message, 'Message sent');
    }

    #[OA\Put(
        path: '/messages/{id}/read',
        summary: 'Mark a message as read',
        tags: ['Messaging'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Message ID')]
    #[OA\Response(response: 200, description: 'Message marked as read')]
    #[OA\Response(response: 404, description: 'Message not found')]
    public function markAsRead(string $id, Request $request): JsonResponse
    {
        $result = $this->messageService->markAsRead($request->user(), $id);

        if (!$result) {
            return $this->error('Message not found', 404);
        }

        return $this->success(null, 'Message marked as read');
    }
}
