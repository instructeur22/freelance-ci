<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\QuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Quotes', description: 'Soumission et gestion des devis')]
class QuoteController extends ApiController
{
    public function __construct(
        protected QuoteService $quoteService
    ) {}

    #[OA\Get(
        path: '/projects/{project}/quotes',
        summary: 'List quotes for a project',
        tags: ['Quotes'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'project', in: 'path', required: true, description: 'Project ID')]
    #[OA\Response(response: 200, description: 'List of quotes', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Quote')),
    ]))]
    public function index(string $project, Request $request): JsonResponse
    {
        $quotes = $this->quoteService->listForProject($request->user(), $project);

        return $this->success($quotes);
    }

    #[OA\Post(
        path: '/projects/{project}/quotes',
        summary: 'Submit a quote for a project',
        tags: ['Quotes'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'project', in: 'path', required: true, description: 'Project ID')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'amount', type: 'number'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'duration', type: 'integer', nullable: true),
        new OA\Property(property: 'duration_unit', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 201, description: 'Quote submitted', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Quote'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to create quote', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function store(string $project, Request $request): JsonResponse
    {
        $quote = $this->quoteService->create($request->user(), $project, $request->all());

        if (!$quote) {
            return $this->error('Unable to create quote', 400);
        }

        return $this->created($quote, 'Quote submitted');
    }

    #[OA\Get(
        path: '/quotes/{id}',
        summary: 'Get a quote by ID',
        tags: ['Quotes'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Quote ID')]
    #[OA\Response(response: 200, description: 'Quote detail', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Quote'),
    ]))]
    #[OA\Response(response: 404, description: 'Quote not found', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function show(string $id, Request $request): JsonResponse
    {
        $quote = $this->quoteService->find($request->user(), $id);

        if (!$quote) {
            return $this->error('Quote not found', 404);
        }

        return $this->success($quote);
    }

    #[OA\Put(
        path: '/quotes/{id}',
        summary: 'Update a quote',
        tags: ['Quotes'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Quote ID')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'amount', type: 'number'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'duration', type: 'integer', nullable: true),
        new OA\Property(property: 'duration_unit', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Quote updated', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Quote'),
    ]))]
    #[OA\Response(response: 400, description: 'Quote not found or cannot be updated', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function update(string $id, Request $request): JsonResponse
    {
        $quote = $this->quoteService->update($request->user(), $id, $request->all());

        if (!$quote) {
            return $this->error('Quote not found or cannot be updated', 400);
        }

        return $this->success($quote, 'Quote updated');
    }

    #[OA\Delete(
        path: '/quotes/{id}',
        summary: 'Withdraw a quote',
        tags: ['Quotes'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Quote ID')]
    #[OA\Response(response: 200, description: 'Quote withdrawn', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 404, description: 'Quote not found or unauthorized', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function destroy(string $id, Request $request): JsonResponse
    {
        $result = $this->quoteService->delete($request->user(), $id);

        if (!$result) {
            return $this->error('Quote not found or unauthorized', 404);
        }

        return $this->success(null, 'Quote withdrawn');
    }

    #[OA\Post(
        path: '/quotes/{id}/accept',
        summary: 'Accept a quote and create a contract',
        tags: ['Quotes'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Quote ID')]
    #[OA\Response(response: 200, description: 'Quote accepted, contract created', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Contract'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to accept quote', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function accept(string $id, Request $request): JsonResponse
    {
        $contract = $this->quoteService->accept($request->user(), $id);

        if (!$contract) {
            return $this->error('Unable to accept quote', 400);
        }

        return $this->success($contract, 'Quote accepted, contract created');
    }

    #[OA\Post(
        path: '/quotes/{id}/refuse',
        summary: 'Refuse a quote',
        tags: ['Quotes'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Quote ID')]
    #[OA\Response(response: 200, description: 'Quote refused', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to refuse quote', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function refuse(string $id, Request $request): JsonResponse
    {
        $result = $this->quoteService->refuse($request->user(), $id);

        if (!$result) {
            return $this->error('Unable to refuse quote', 400);
        }

        return $this->success(null, 'Quote refused');
    }
}
