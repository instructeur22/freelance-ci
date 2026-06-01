<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Reviews', description: 'Avis et évaluations')]
class ReviewController extends ApiController
{
    public function __construct(
        protected ReviewService $reviewService
    ) {}

    #[OA\Post(
        path: '/contracts/{contract}/review',
        summary: 'Create a review for a contract',
        tags: ['Reviews'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'contract', in: 'path', required: true, description: 'Contract ID')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5),
        new OA\Property(property: 'comment', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 201, description: 'Review submitted', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Review'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to create review', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function store(string $contract, Request $request): JsonResponse
    {
        $review = $this->reviewService->create($request->user(), $contract, $request->all());

        if (!$review) {
            return $this->error('Unable to create review', 400);
        }

        return $this->created($review, 'Review submitted');
    }

    #[OA\Get(
        path: '/freelances/{freelance}/reviews',
        summary: 'List reviews for a freelance',
        tags: ['Reviews'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'freelance', in: 'path', required: true, description: 'Freelance user ID')]
    #[OA\Response(response: 200, description: 'List of reviews', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Review')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function freelanceReviews(string $freelance, Request $request): JsonResponse
    {
        $reviews = $this->reviewService->listForFreelance($freelance);

        return $this->paginated($reviews);
    }

    #[OA\Post(
        path: '/reviews/{review}/reply',
        summary: 'Reply to a review',
        tags: ['Reviews'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'review', in: 'path', required: true, description: 'Review ID')]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'comment', type: 'string'),
    ]))]
    #[OA\Response(response: 200, description: 'Reply submitted', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Review'),
    ]))]
    #[OA\Response(response: 400, description: 'Unable to reply to review', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function reply(string $review, Request $request): JsonResponse
    {
        $result = $this->reviewService->reply($request->user(), $review, $request->all());

        if (!$result) {
            return $this->error('Unable to reply to review', 400);
        }

        return $this->success($result, 'Reply submitted');
    }
}
