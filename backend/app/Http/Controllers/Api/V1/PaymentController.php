<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Payments', description: 'Paiements via Genius Pay')]
class PaymentController extends ApiController
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    #[OA\Post(
        path: '/payments/initiate',
        summary: 'Initiate a payment via Genius Pay',
        tags: ['Payments'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Payment initiated')]
    #[OA\Response(response: 400, description: 'Unable to initiate payment')]
    public function initiate(Request $request): JsonResponse
    {
        $payment = $this->paymentService->initiate($request->user(), $request->all());

        if (!$payment) {
            return $this->error('Unable to initiate payment', 400);
        }

        return $this->success($payment, 'Payment initiated');
    }

    #[OA\Post(
        path: '/payments/{id}/confirm',
        summary: 'Confirm a payment',
        tags: ['Payments'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Payment ID')]
    #[OA\Response(response: 200, description: 'Payment confirmed')]
    #[OA\Response(response: 400, description: 'Unable to confirm payment')]
    public function confirm(string $id, Request $request): JsonResponse
    {
        $payment = $this->paymentService->confirm($request->user(), $id, $request->all());

        if (!$payment) {
            return $this->error('Unable to confirm payment', 400);
        }

        return $this->success($payment, 'Payment confirmed');
    }

    #[OA\Get(
        path: '/payments/{id}',
        summary: 'Get payment details',
        tags: ['Payments'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Payment ID')]
    #[OA\Response(response: 200, description: 'Payment detail')]
    #[OA\Response(response: 404, description: 'Payment not found')]
    public function show(string $id, Request $request): JsonResponse
    {
        $payment = $this->paymentService->find($request->user(), $id);

        if (!$payment) {
            return $this->error('Payment not found', 404);
        }

        return $this->success($payment);
    }

    #[OA\Get(
        path: '/payments',
        summary: 'List payments for authenticated user',
        tags: ['Payments'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of payments')]
    public function index(Request $request): JsonResponse
    {
        $payments = $this->paymentService->listForUser($request->user());

        return $this->success($payments);
    }

    #[OA\Post(
        path: '/webhooks/genius-pay',
        summary: 'Genius Pay webhook callback',
        tags: ['Payments'],
    )]
    #[OA\Response(response: 200, description: 'Webhook processed')]
    #[OA\Response(response: 400, description: 'Webhook processing failed')]
    public function webhook(Request $request): JsonResponse
    {
        $result = $this->paymentService->handleWebhook($request);

        if (!$result) {
            return $this->error('Webhook processing failed', 400);
        }

        return $this->success(null, 'Webhook processed');
    }
}
