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
    #[OA\Response(response: 200, description: 'Full profile data')]
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
    #[OA\Response(response: 200, description: 'Profile updated')]
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
    #[OA\Response(response: 200, description: 'Client profile data')]
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
    #[OA\Response(response: 200, description: 'Client profile updated')]
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
    #[OA\Response(response: 200, description: 'Freelance profile data')]
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
    #[OA\Response(response: 200, description: 'Freelance profile updated')]
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
    #[OA\Response(response: 200, description: 'Skill added')]
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
    #[OA\Response(response: 200, description: 'Skill removed')]
    #[OA\Response(response: 404, description: 'Skill not found')]
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
    #[OA\Response(response: 201, description: 'Portfolio item added')]
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
    #[OA\Response(response: 200, description: 'Portfolio item removed')]
    #[OA\Response(response: 404, description: 'Portfolio item not found')]
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
    #[OA\Response(response: 200, description: 'List of freelances')]
    public function freelanceList(Request $request): JsonResponse
    {
        $freelances = $this->profileService->listFreelances($request->all());

        return $this->success($freelances);
    }

    #[OA\Get(
        path: '/freelances/{id}',
        summary: 'Get freelance detail (public)',
        tags: ['Profiles'],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Freelance user ID')]
    #[OA\Response(response: 200, description: 'Freelance detail')]
    #[OA\Response(response: 404, description: 'Freelance not found')]
    public function freelanceDetail(string $id): JsonResponse
    {
        $freelance = $this->profileService->getFreelanceDetail($id);

        if (!$freelance) {
            return $this->error('Freelance not found', 404);
        }

        return $this->success($freelance);
    }
}
