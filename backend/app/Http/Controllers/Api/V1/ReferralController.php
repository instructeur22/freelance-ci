<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Referrals', description: 'Programme de parrainage')]
class ReferralController extends ApiController
{
    public function __construct(
        protected ReferralService $referralService
    ) {}

    #[OA\Get(
        path: '/referrals/code',
        summary: 'Get your referral code',
        tags: ['Referrals'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Referral code', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', properties: [
            new OA\Property(property: 'code', type: 'string'),
        ], type: 'object'),
    ]))]
    public function code(Request $request): JsonResponse
    {
        $code = $this->referralService->getOrCreateCode($request->user());

        return $this->success(["code" => $code->code]);
    }

    #[OA\Get(
        path: '/referrals/stats',
        summary: 'Get referral statistics',
        tags: ['Referrals'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Referral stats', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', properties: [
            new OA\Property(property: 'code', type: 'string', nullable: true),
            new OA\Property(property: 'total_referrals', type: 'integer'),
            new OA\Property(property: 'completed_referrals', type: 'integer'),
            new OA\Property(property: 'total_earned', type: 'number', format: 'float'),
            new OA\Property(property: 'reward_per_referral', type: 'number', format: 'float'),
        ], type: 'object'),
    ]))]
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->referralService->getReferralStats($request->user());

        return $this->success($stats);
    }

    #[OA\Get(
        path: '/referrals',
        summary: 'List referrals made by you',
        tags: ['Referrals'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of referrals', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Referral')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function index(Request $request): JsonResponse
    {
        $referrals = $this->referralService->getReferrals($request->user());

        return $this->paginated($referrals);
    }
}
