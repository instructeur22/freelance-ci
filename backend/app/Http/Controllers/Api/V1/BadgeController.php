<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\BadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Badges', description: 'Badge vérifié (achat et statut)')]
class BadgeController extends ApiController
{
    public function __construct(
        protected BadgeService $badgeService
    ) {}

    #[OA\Post(
        path: '/badges/purchase',
        summary: 'Initiate verified badge purchase via Genius Pay',
        tags: ['Badges'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'payment_method', type: 'string', enum: ['mobile_money', 'card']),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Badge purchase initiated', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', properties: [
            new OA\Property(property: 'transaction', ref: '#/components/schemas/Transaction'),
            new OA\Property(property: 'payment_url', type: 'string', nullable: true),
            new OA\Property(property: 'reference', type: 'string', nullable: true),
        ], type: 'object'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to purchase badge')]
    public function purchase(Request $request): JsonResponse
    {
        $result = $this->badgeService->purchase($request->user());

        if (!$result) {
            return $this->error('Unable to initiate badge purchase', 400);
        }

        return $this->success($result, 'Badge purchase initiated');
    }

    #[OA\Get(
        path: '/badges',
        summary: 'Get current badge status for authenticated user',
        tags: ['Badges'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Badge status', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/VerifiedBadge'),
    ]))]
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->freelanceProfile;

        if (!$profile) {
            return $this->error('Freelance profile not found', 404);
        }

        $badge = $this->badgeService->getActiveBadge($profile);

        return $this->success($badge);
    }
}
