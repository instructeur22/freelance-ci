<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Projects', description: 'Gestion des projets')]
class ProjectController extends ApiController
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    #[OA\Get(
        path: '/projects',
        summary: 'List all projects (public)',
        tags: ['Projects'],
    )]
    #[OA\Response(response: 200, description: 'List of projects')]
    public function index(Request $request): JsonResponse
    {
        $projects = $this->projectService->list($request->all());

        return $this->success($projects);
    }

    #[OA\Get(
        path: '/projects/{id}',
        summary: 'Get project detail (public)',
        tags: ['Projects'],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Project ID')]
    #[OA\Response(response: 200, description: 'Project detail')]
    #[OA\Response(response: 404, description: 'Project not found')]
    public function show(string $id): JsonResponse
    {
        $project = $this->projectService->find($id);

        if (!$project) {
            return $this->error('Project not found', 404);
        }

        return $this->success($project);
    }

    #[OA\Post(
        path: '/projects',
        summary: 'Create a new project',
        tags: ['Projects'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Response(response: 201, description: 'Project created')]
    public function store(Request $request): JsonResponse
    {
        $project = $this->projectService->create($request->user(), $request->all());

        return $this->created($project, 'Project created');
    }

    #[OA\Put(
        path: '/projects/{id}',
        summary: 'Update a project',
        tags: ['Projects'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Project ID')]
    #[OA\Response(response: 200, description: 'Project updated')]
    #[OA\Response(response: 404, description: 'Project not found or unauthorized')]
    public function update(string $id, Request $request): JsonResponse
    {
        $project = $this->projectService->update($request->user(), $id, $request->all());

        if (!$project) {
            return $this->error('Project not found or unauthorized', 404);
        }

        return $this->success($project, 'Project updated');
    }

    #[OA\Delete(
        path: '/projects/{id}',
        summary: 'Delete a project',
        tags: ['Projects'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Project ID')]
    #[OA\Response(response: 200, description: 'Project deleted')]
    #[OA\Response(response: 404, description: 'Project not found or unauthorized')]
    public function destroy(string $id, Request $request): JsonResponse
    {
        $result = $this->projectService->delete($request->user(), $id);

        if (!$result) {
            return $this->error('Project not found or unauthorized', 404);
        }

        return $this->success(null, 'Project deleted');
    }

    #[OA\Post(
        path: '/projects/{id}/files',
        summary: 'Add a file to a project',
        tags: ['Projects'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Project ID')]
    #[OA\Response(response: 201, description: 'File added')]
    #[OA\Response(response: 404, description: 'Project not found or unauthorized')]
    public function addFile(string $id, Request $request): JsonResponse
    {
        $file = $this->projectService->addFile($request->user(), $id, $request->all());

        if (!$file) {
            return $this->error('Project not found or unauthorized', 404);
        }

        return $this->created($file, 'File added');
    }

    #[OA\Delete(
        path: '/projects/{project}/files/{file}',
        summary: 'Remove a file from a project',
        tags: ['Projects'],
        security: [['BearerToken' => []]],
    )]
    #[OA\Parameter(name: 'project', in: 'path', required: true, description: 'Project ID')]
    #[OA\Parameter(name: 'file', in: 'path', required: true, description: 'File ID')]
    #[OA\Response(response: 200, description: 'File removed')]
    #[OA\Response(response: 404, description: 'File not found or unauthorized')]
    public function removeFile(string $project, string $file, Request $request): JsonResponse
    {
        $result = $this->projectService->removeFile($request->user(), $project, $file);

        if (!$result) {
            return $this->error('File not found or unauthorized', 404);
        }

        return $this->success(null, 'File removed');
    }
}
