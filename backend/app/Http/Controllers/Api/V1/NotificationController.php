<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Notifications', description: 'Gestion des notifications')]
class NotificationController extends ApiController
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    #[OA\Get(
        path: '/notifications',
        summary: 'List notifications',
        tags: ['Notifications'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of notifications', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Notification')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notificationService->listForUser($request->user());

        return $this->paginated($notifications);
    }

    #[OA\Put(
        path: '/notifications/{id}/read',
        summary: 'Mark a notification as read',
        tags: ['Notifications'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Notification ID')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: []))]
    #[OA\Response(response: 200, description: 'Notification marked as read', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 404, description: 'Notification not found', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function markAsRead(string $id, Request $request): JsonResponse
    {
        $result = $this->notificationService->markAsRead($request->user(), $id);

        if (!$result) {
            return $this->error('Notification not found', 404);
        }

        return $this->success(null, 'Notification marked as read');
    }

    #[OA\Put(
        path: '/notifications/read-all',
        summary: 'Mark all notifications as read',
        tags: ['Notifications'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'All notifications marked as read', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return $this->success(null, 'All notifications marked as read');
    }

    #[OA\Delete(
        path: '/notifications/{id}',
        summary: 'Delete a notification',
        tags: ['Notifications'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Notification ID')]
    #[OA\Response(response: 200, description: 'Notification deleted', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 404, description: 'Notification not found', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function destroy(string $id, Request $request): JsonResponse
    {
        $result = $this->notificationService->delete($request->user(), $id);

        if (!$result) {
            return $this->error('Notification not found', 404);
        }

        return $this->success(null, 'Notification deleted');
    }
}
