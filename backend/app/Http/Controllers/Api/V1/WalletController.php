<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Wallet', description: 'Gestion du portefeuille')]
class WalletController extends ApiController
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    #[OA\Get(
        path: '/wallet',
        summary: 'Get wallet balance and details',
        tags: ['Wallet'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Wallet details', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Wallet'),
    ]))]
    public function show(Request $request): JsonResponse
    {
        $wallet = $this->walletService->getWallet($request->user());

        return $this->success($wallet);
    }

    #[OA\Get(
        path: '/wallet/transactions',
        summary: 'List wallet transactions',
        tags: ['Wallet'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of transactions', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/WalletTransaction')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function transactions(Request $request): JsonResponse
    {
        $transactions = $this->walletService->getTransactions($request->user());

        return $this->paginated($transactions);
    }

    #[OA\Post(
        path: '/wallet/withdraw',
        summary: 'Request a withdrawal',
        tags: ['Wallet'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'amount', type: 'number'),
        new OA\Property(property: 'phone', type: 'string'),
        new OA\Property(property: 'payment_method', type: 'string', default: 'mobile_money'),
    ]))]
    #[OA\Response(response: 200, description: 'Withdrawal requested', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to process withdrawal', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function withdraw(Request $request): JsonResponse
    {
        $result = $this->walletService->requestWithdrawal($request->user(), $request->all());

        if (!$result) {
            return $this->error('Unable to process withdrawal', 400);
        }

        return $this->success($result, 'Withdrawal requested');
    }
}
