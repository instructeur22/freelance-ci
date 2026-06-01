<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Profiles', description: 'Gestion des profils utilisateur')]
class ProfileController extends ApiController
{
    public function __construct(
        protected ProfileService $profileService
    ) {}

    #[OA\Get(
        path: '/profiles/me',
        summary: 'Get full profile of authenticated user',
        tags: ['Profiles'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Full profile data', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/User'),
    ]))]
    public function me(Request $request): JsonResponse
    {
        $profile = $this->profileService->getFullProfile($request->user());

        return $this->success($profile);
    }

    #[OA\Put(
        path: '/profiles/me',
        summary: 'Update common profile fields',
        tags: ['Profiles'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'phone', type: 'string', nullable: true),
        new OA\Property(property: 'bio', type: 'string', nullable: true),
        new OA\Property(property: 'avatar_url', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Profile updated', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Profile'),
    ]))]
    public function updateMe(Request $request): JsonResponse
    {
        $profile = $this->profileService->updateCommonProfile($request->user(), $request->all());

        return $this->success($profile, 'Profile updated');
    }

    #[OA\Get(
        path: '/profiles/client',
        summary: 'Get client profile',
        tags: ['Profiles'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Client profile data', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/ClientProfile'),
    ]))]
    public function clientProfile(Request $request): JsonResponse
    {
        $profile = $this->profileService->getClientProfile($request->user());

        return $this->success($profile);
    }

    #[OA\Put(
        path: '/profiles/client',
        summary: 'Update client profile',
        tags: ['Profiles'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'company_name', type: 'string', nullable: true),
        new OA\Property(property: 'company_website', type: 'string', nullable: true),
        new OA\Property(property: 'company_description', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Client profile updated', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/ClientProfile'),
    ]))]
    public function updateClientProfile(Request $request): JsonResponse
    {
        $profile = $this->profileService->updateClientProfile($request->user(), $request->all());

        return $this->success($profile, 'Client profile updated');
    }

    #[OA\Get(
        path: '/profiles/freelance',
        summary: 'Get freelance profile',
        tags: ['Profiles'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'Freelance profile data', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/FreelanceProfile'),
    ]))]
    public function freelanceProfile(Request $request): JsonResponse
    {
        $profile = $this->profileService->getFreelanceProfile($request->user());

        return $this->success($profile);
    }

    #[OA\Put(
        path: '/profiles/freelance',
        summary: 'Update freelance profile',
        tags: ['Profiles'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'hourly_rate', type: 'number', nullable: true),
        new OA\Property(property: 'availability', type: 'string', nullable: true),
        new OA\Property(property: 'languages', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Freelance profile updated', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/FreelanceProfile'),
    ]))]
    public function updateFreelanceProfile(Request $request): JsonResponse
    {
        $profile = $this->profileService->updateFreelanceProfile($request->user(), $request->all());

        return $this->success($profile, 'Freelance profile updated');
    }

    #[OA\Post(
        path: '/profiles/freelance/skills',
        summary: 'Add a skill to freelance profile',
        tags: ['Profiles'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'skill_id', type: 'string', format: 'uuid'),
    ]))]
    #[OA\Response(response: 200, description: 'Skill added', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function addSkill(Request $request): JsonResponse
    {
        $result = $this->profileService->addSkill($request->user(), $request->all());

        return $this->success($result, 'Skill added');
    }

    #[OA\Delete(
        path: '/profiles/freelance/skills/{skill}',
        summary: 'Remove a skill from freelance profile',
        tags: ['Profiles'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'skill', in: 'path', required: true, description: 'Skill ID')]
    #[OA\Response(response: 200, description: 'Skill removed', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 404, description: 'Skill not found', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function removeSkill(string $skill, Request $request): JsonResponse
    {
        $result = $this->profileService->removeSkill($request->user(), $skill);

        if (!$result) {
            return $this->error('Skill not found', 404);
        }

        return $this->success(null, 'Skill removed');
    }

    #[OA\Post(
        path: '/profiles/freelance/portfolio',
        summary: 'Add a portfolio item',
        tags: ['Profiles'],
        security: [['BearerToken' => []]],
    )]
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'link', type: 'string', nullable: true),
    ]))]
    #[OA\Response(response: 201, description: 'Portfolio item added', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/PortfolioItem'),
    ]))]
    public function addPortfolioItem(Request $request): JsonResponse
    {
        $item = $this->profileService->addPortfolioItem($request->user(), $request->all());

        return $this->created($item, 'Portfolio item added');
    }

    #[OA\Delete(
        path: '/profiles/freelance/portfolio/{item}',
        summary: 'Remove a portfolio item',
        tags: ['Profiles'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'item', in: 'path', required: true, description: 'Portfolio item ID')]
    #[OA\Response(response: 200, description: 'Portfolio item removed', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 404, description: 'Portfolio item not found', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function removePortfolioItem(string $item, Request $request): JsonResponse
    {
        $result = $this->profileService->removePortfolioItem($request->user(), $item);

        if (!$result) {
            return $this->error('Portfolio item not found', 404);
        }

        return $this->success(null, 'Portfolio item removed');
    }

    #[OA\Get(
        path: '/freelances',
        summary: 'List all freelances (public)',
        tags: ['Profiles'],
    )]
    #[OA\Response(response: 200, description: 'List of freelances', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/User')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function freelanceList(Request $request): JsonResponse
    {
        $freelances = $this->profileService->listFreelances($request->all());

        return $this->paginated($freelances);
    }

    #[OA\Get(
        path: '/freelances/{id}',
        summary: 'Get freelance detail (public)',
        tags: ['Profiles'],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Freelance user ID')]
    #[OA\Response(response: 200, description: 'Freelance detail', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/User'),
    ]))]
    #[OA\Response(response: 404, description: 'Freelance not found', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function freelanceDetail(string $id): JsonResponse
    {
        $freelance = $this->profileService->getFreelanceDetail($id);

        if (!$freelance) {
            return $this->error('Freelance not found', 404);
        }

        return $this->success($freelance);
    }
}
