<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Subscriptions', description: 'Abonnements freelances (Starter, Pro, Expert)')]
class SubscriptionController extends ApiController
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    #[OA\Get(
        path: '/subscriptions/plans',
        summary: 'List available subscription plans',
        tags: ['Subscriptions'],
    )]
    #[OA\Response(response: 200, description: 'List of plans', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/SubscriptionPlanConfig')),
    ]))]
    public function plans(): JsonResponse
    {
        $plans = $this->subscriptionService->getPlans();

        return $this->success($plans);
    }

    #[OA\Post(
        path: '/subscriptions/purchase',
        summary: 'Subscribe to a plan (free = instant, paid = Genius Pay)',
        tags: ['Subscriptions'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(required: ['plan'], properties: [
        new OA\Property(property: 'plan', type: 'string', enum: ['starter', 'pro', 'expert']),
        new OA\Property(property: 'billing_cycle', type: 'string', enum: ['monthly', 'yearly'], default: 'monthly'),
        new OA\Property(property: 'payment_method', type: 'string', enum: ['mobile_money', 'card'], nullable: true),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Subscription created', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', oneOf: [
            new OA\Schema(ref: '#/components/schemas/FreelanceSubscription'),
            new OA\Schema(properties: [
                new OA\Property(property: 'transaction', ref: '#/components/schemas/Transaction'),
                new OA\Property(property: 'payment_url', type: 'string', nullable: true),
                new OA\Property(property: 'reference', type: 'string', nullable: true),
            ]),
        ]),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to process subscription')]
    public function purchase(Request $request): JsonResponse
    {
        $result = $this->subscriptionService->purchase($request->user(), $request->all());

        if (!$result) {
            return $this->error('Unable to process subscription', 400);
        }

        return $this->success($result, 'Subscription processed');
    }

    #[OA\Get(
        path: '/subscriptions',
        summary: 'Get current active subscription',
        tags: ['Subscriptions'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Current subscription', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/FreelanceSubscription'),
    ]))]
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->freelanceProfile;

        if (!$profile) {
            return $this->error('Freelance profile not found', 404);
        }

        $subscription = $this->subscriptionService->getCurrent($profile);

        return $this->success($subscription);
    }

    #[OA\Post(
        path: '/subscriptions/cancel',
        summary: 'Cancel current subscription',
        tags: ['Subscriptions'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Subscription cancelled', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/FreelanceSubscription'),
    ]))]
    #[OA\Response(response: 400, description: 'No active subscription to cancel')]
    public function cancel(Request $request): JsonResponse
    {
        $result = $this->subscriptionService->cancel($request->user());

        if (!$result) {
            return $this->error('No active subscription to cancel', 400);
        }

        return $this->success($result, 'Subscription cancelled');
    }

    #[OA\Post(
        path: '/subscriptions/upgrade',
        summary: 'Upgrade to a higher plan',
        tags: ['Subscriptions'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(required: ['plan'], properties: [
        new OA\Property(property: 'plan', type: 'string', enum: ['pro', 'expert']),
        new OA\Property(property: 'billing_cycle', type: 'string', enum: ['monthly', 'yearly'], default: 'monthly'),
    ]))]
    #[OA\Response(response: 200, description: 'Plan upgraded', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/FreelanceSubscription'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to upgrade plan')]
    public function upgrade(Request $request): JsonResponse
    {
        $result = $this->subscriptionService->upgrade(
            $request->user(),
            $request->input('plan'),
            $request->input('billing_cycle', 'monthly')
        );

        if (!$result) {
            return $this->error('Unable to upgrade plan', 400);
        }

        return $this->success($result, 'Plan upgraded');
    }
}
