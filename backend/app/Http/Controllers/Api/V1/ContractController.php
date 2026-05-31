<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ContractService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Contracts', description: 'Gestion des contrats et jalons')]
class ContractController extends ApiController
{
    public function __construct(
        protected ContractService $contractService
    ) {}

    #[OA\Get(
        path: '/contracts',
        summary: 'List contracts for authenticated user',
        tags: ['Contracts'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 200, description: 'List of contracts')]
    public function index(Request $request): JsonResponse
    {
        $contracts = $this->contractService->listForUser($request->user());

        return $this->success($contracts);
    }

    #[OA\Get(
        path: '/contracts/{id}',
        summary: 'Get a contract by ID',
        tags: ['Contracts'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Contract ID')]
    #[OA\Response(response: 200, description: 'Contract detail')]
    #[OA\Response(response: 404, description: 'Contract not found')]
    public function show(string $id, Request $request): JsonResponse
    {
        $contract = $this->contractService->find($request->user(), $id);

        if (!$contract) {
            return $this->error('Contract not found', 404);
        }

        return $this->success($contract);
    }

    #[OA\Post(
        path: '/contracts/{id}/sign',
        summary: 'Sign a contract',
        tags: ['Contracts'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Contract ID')]
    #[OA\Response(response: 200, description: 'Contract signed')]
    #[OA\Response(response: 400, description: 'Unable to sign contract')]
    public function sign(string $id, Request $request): JsonResponse
    {
        $contract = $this->contractService->sign($request->user(), $id);

        if (!$contract) {
            return $this->error('Unable to sign contract', 400);
        }

        return $this->success($contract, 'Contract signed');
    }

    #[OA\Post(
        path: '/contracts/{id}/milestones',
        summary: 'Add a milestone to a contract',
        tags: ['Contracts'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Contract ID')]
    #[OA\Response(response: 201, description: 'Milestone added')]
    #[OA\Response(response: 400, description: 'Unable to add milestone')]
    public function addMilestone(string $id, Request $request): JsonResponse
    {
        $milestone = $this->contractService->addMilestone($request->user(), $id, $request->all());

        if (!$milestone) {
            return $this->error('Unable to add milestone', 400);
        }

        return $this->created($milestone, 'Milestone added');
    }

    #[OA\Put(
        path: '/contracts/{contract}/milestones/{milestone}',
        summary: 'Update a milestone',
        tags: ['Contracts'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'contract', in: 'path', required: true, description: 'Contract ID')]
    #[OA\Parameter(name: 'milestone', in: 'path', required: true, description: 'Milestone ID')]
    #[OA\Response(response: 200, description: 'Milestone updated')]
    #[OA\Response(response: 400, description: 'Unable to update milestone')]
    public function updateMilestone(string $contract, string $milestone, Request $request): JsonResponse
    {
        $result = $this->contractService->updateMilestone($request->user(), $contract, $milestone, $request->all());

        if (!$result) {
            return $this->error('Unable to update milestone', 400);
        }

        return $this->success($result, 'Milestone updated');
    }

    #[OA\Post(
        path: '/contracts/{contract}/milestones/{milestone}/deliver',
        summary: 'Mark a milestone as delivered',
        tags: ['Contracts'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'contract', in: 'path', required: true, description: 'Contract ID')]
    #[OA\Parameter(name: 'milestone', in: 'path', required: true, description: 'Milestone ID')]
    #[OA\Response(response: 200, description: 'Milestone marked as delivered')]
    #[OA\Response(response: 400, description: 'Unable to mark milestone as delivered')]
    public function deliverMilestone(string $contract, string $milestone, Request $request): JsonResponse
    {
        $result = $this->contractService->deliverMilestone($request->user(), $contract, $milestone);

        if (!$result) {
            return $this->error('Unable to mark milestone as delivered', 400);
        }

        return $this->success($result, 'Milestone marked as delivered');
    }

    #[OA\Post(
        path: '/contracts/{contract}/milestones/{milestone}/validate',
        summary: 'Validate a delivered milestone',
        tags: ['Contracts'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'contract', in: 'path', required: true, description: 'Contract ID')]
    #[OA\Parameter(name: 'milestone', in: 'path', required: true, description: 'Milestone ID')]
    #[OA\Response(response: 200, description: 'Milestone validated')]
    #[OA\Response(response: 400, description: 'Unable to validate milestone')]
    public function validateMilestone(string $contract, string $milestone, Request $request): JsonResponse
    {
        $result = $this->contractService->validateMilestone($request->user(), $contract, $milestone);

        if (!$result) {
            return $this->error('Unable to validate milestone', 400);
        }

        return $this->success($result, 'Milestone validated');
    }
}
