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
    #[OA\Response(response: 200, description: 'List of conversations', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Conversation')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function conversations(Request $request): JsonResponse
    {
        $conversations = $this->messageService->listConversations($request->user());

        return $this->paginated($conversations);
    }

    #[OA\Post(
        path: '/conversations',
        summary: 'Start a new conversation',
        tags: ['Messaging'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'participant_id', type: 'string', format: 'uuid', description: 'ID of the user to converse with'),
    ]))]
    #[OA\Response(response: 201, description: 'Conversation started', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Conversation'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to start conversation', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
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
    #[OA\Response(response: 200, description: 'List of messages', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Message')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    #[OA\Response(response: 404, description: 'Conversation not found', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function messages(string $id, Request $request): JsonResponse
    {
        $messages = $this->messageService->getMessages($request->user(), $id);

        if ($messages === null) {
            return $this->error('Conversation not found', 404);
        }

        return $this->paginated($messages);
    }

    #[OA\Post(
        path: '/conversations/{id}/messages',
        summary: 'Send a message in a conversation',
        tags: ['Messaging'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Conversation ID')]
    #[OA\RequestBody(content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(properties: [
        new OA\Property(property: 'content', type: 'string'),
        new OA\Property(property: 'file', type: 'string', format: 'binary', nullable: true),
    ])))]
    #[OA\Response(response: 201, description: 'Message sent', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Message'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to send message', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
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
    #[OA\Response(response: 200, description: 'Message marked as read', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 404, description: 'Message not found', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function markAsRead(string $id, Request $request): JsonResponse
    {
        $result = $this->messageService->markAsRead($request->user(), $id);

        if (!$result) {
            return $this->error('Message not found', 404);
        }

        return $this->success(null, 'Message marked as read');
    }
}
