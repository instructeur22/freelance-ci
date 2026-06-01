<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\BoostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Boosts', description: 'Boost de profil / projet (visibilité)')]
class BoostController extends ApiController
{
    public function __construct(
        protected BoostService $boostService
    ) {}

    #[OA\Post(
        path: '/boosts/purchase',
        summary: 'Initiate a boost purchase via Genius Pay',
        tags: ['Boosts'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(required: ['target_type', 'duration'], properties: [
        new OA\Property(property: 'target_type', type: 'string', enum: ['profile', 'project']),
        new OA\Property(property: 'duration', type: 'string', enum: ['7_days', '30_days']),
        new OA\Property(property: 'target_id', type: 'string', format: 'uuid', nullable: true, description: 'Project UUID (required if target_type=project)'),
        new OA\Property(property: 'payment_method', type: 'string', enum: ['mobile_money', 'card']),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Boost purchase initiated', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', properties: [
            new OA\Property(property: 'transaction', ref: '#/components/schemas/Transaction'),
            new OA\Property(property: 'payment_url', type: 'string', nullable: true),
            new OA\Property(property: 'reference', type: 'string', nullable: true),
        ], type: 'object'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to initiate boost purchase')]
    public function purchase(Request $request): JsonResponse
    {
        $result = $this->boostService->purchase($request->user(), $request->all());

        if (!$result) {
            return $this->error('Unable to initiate boost purchase', 400);
        }

        return $this->success($result, 'Boost purchase initiated');
    }

    #[OA\Get(
        path: '/boosts',
        summary: 'List boosts for authenticated user',
        tags: ['Boosts'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of boosts', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Boost')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function index(Request $request): JsonResponse
    {
        $boosts = $this->boostService->listForUser($request->user());

        return $this->paginated($boosts);
    }
}
