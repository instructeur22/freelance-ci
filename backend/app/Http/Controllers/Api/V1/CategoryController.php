<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Categories', description: 'Catégories et compétences')]
class CategoryController extends ApiController
{
    public function __construct(
        protected CategoryService $categoryService
    ) {}

    #[OA\Get(
        path: '/categories',
        summary: 'List all categories',
        tags: ['Categories'],
    )]
    #[OA\Response(response: 200, description: 'List of categories', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Category')),
    ]))]
    public function index(): JsonResponse
    {
        $categories = $this->categoryService->listAll();

        return $this->success($categories);
    }

    #[OA\Get(
        path: '/categories/{id}/skills',
        summary: 'List skills for a category',
        tags: ['Categories'],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Category ID')]
    #[OA\Response(response: 200, description: 'List of skills', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Skill')),
    ]))]
    #[OA\Response(response: 404, description: 'Category not found', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function skills(string $id): JsonResponse
    {
        $skills = $this->categoryService->getSkills($id);

        if ($skills === null) {
            return $this->error('Category not found', 404);
        }

        return $this->success($skills);
    }
}
