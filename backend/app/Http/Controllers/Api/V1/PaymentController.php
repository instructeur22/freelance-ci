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
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'amount', type: 'number'),
        new OA\Property(property: 'currency', type: 'string', default: 'XOF'),
        new OA\Property(property: 'payment_method', type: 'string', enum: ['mobile_money', 'card']),
        new OA\Property(property: 'phone', type: 'string', description: 'Phone number for mobile money'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'metadata', type: 'object', nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Payment initiated', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Payment'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to initiate payment', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
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
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'transaction_id', type: 'string', description: 'Genius Pay transaction ID'),
        new OA\Property(property: 'otp', type: 'string', nullable: true, description: 'OTP code if required'),
    ]))]
    #[OA\Response(response: 200, description: 'Payment confirmed', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Payment'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to confirm payment', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
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
    #[OA\Response(response: 200, description: 'Payment detail', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Payment'),
    ]))]
    #[OA\Response(response: 404, description: 'Payment not found', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
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
    #[OA\Response(response: 200, description: 'List of payments', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Payment')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function index(Request $request): JsonResponse
    {
        $payments = $this->paymentService->listForUser($request->user());

        return $this->paginated($payments);
    }

    #[OA\Post(
        path: '/webhooks/genius-pay',
        summary: 'Genius Pay webhook callback',
        tags: ['Payments'],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'transaction_id', type: 'string'),
        new OA\Property(property: 'status', type: 'string'),
        new OA\Property(property: 'amount', type: 'number'),
        new OA\Property(property: 'phone', type: 'string'),
        new OA\Property(property: 'reference', type: 'string'),
    ]))]
    #[OA\Response(response: 200, description: 'Webhook processed', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 400, description: 'Webhook processing failed', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function webhook(Request $request): JsonResponse
    {
        $result = $this->paymentService->handleWebhook($request);

        if (!$result) {
            return $this->error('Webhook processing failed', 400);
        }

        return $this->success(null, 'Webhook processed');
    }
}
