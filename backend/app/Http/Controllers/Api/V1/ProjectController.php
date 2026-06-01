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
    #[OA\Response(response: 200, description: 'List of projects', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Project')),
        new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
    ]))]
    public function index(Request $request): JsonResponse
    {
        $projects = $this->projectService->list($request->all());

        return $this->paginated($projects);
    }

    #[OA\Get(
        path: '/projects/{id}',
        summary: 'Get project detail (public)',
        tags: ['Projects'],
    )]
    #[OA\Parameter(name: 'id', in: 'path', required: true, description: 'Project ID')]
    #[OA\Response(response: 200, description: 'Project detail', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Project'),
    ]))]
    #[OA\Response(response: 404, description: 'Project not found', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
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
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'budget_min', type: 'number', nullable: true),
        new OA\Property(property: 'budget_max', type: 'number', nullable: true),
        new OA\Property(property: 'currency', type: 'string', default: 'XOF'),
        new OA\Property(property: 'duration', type: 'integer', nullable: true),
        new OA\Property(property: 'duration_unit', type: 'string', enum: ['days', 'weeks', 'months'], nullable: true),
        new OA\Property(property: 'project_type', type: 'string', nullable: true),
        new OA\Property(property: 'difficulty', type: 'string', nullable: true),
        new OA\Property(property: 'category_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'skills', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
    ]))]
    #[OA\Response(response: 201, description: 'Project created', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Project'),
    ]))]
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
    #[OA\RequestBody(content: new OA\JsonContent(properties: [
        new OA\Property(property: 'title', type: 'string'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'budget_min', type: 'number', nullable: true),
        new OA\Property(property: 'budget_max', type: 'number', nullable: true),
        new OA\Property(property: 'currency', type: 'string'),
        new OA\Property(property: 'duration', type: 'integer', nullable: true),
        new OA\Property(property: 'duration_unit', type: 'string', nullable: true),
        new OA\Property(property: 'project_type', type: 'string', nullable: true),
        new OA\Property(property: 'difficulty', type: 'string', nullable: true),
        new OA\Property(property: 'category_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'status', type: 'string', nullable: true),
        new OA\Property(property: 'skills', type: 'array', items: new OA\Items(type: 'string'), nullable: true),
    ]))]
    #[OA\Response(response: 200, description: 'Project updated', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/Project'),
    ]))]
    #[OA\Response(response: 404, description: 'Project not found or unauthorized', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
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
    #[OA\Response(response: 200, description: 'Project deleted', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 404, description: 'Project not found or unauthorized', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
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
    #[OA\RequestBody(content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(properties: [
        new OA\Property(property: 'file', type: 'string', format: 'binary'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
    ])))]
    #[OA\Response(response: 201, description: 'File added', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'data', ref: '#/components/schemas/ProjectFile'),
    ]))]
    #[OA\Response(response: 404, description: 'Project not found or unauthorized', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
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
    #[OA\Response(response: 200, description: 'File removed', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    #[OA\Response(response: 404, description: 'File not found or unauthorized', content: new OA\JsonContent(properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]))]
    public function removeFile(string $project, string $file, Request $request): JsonResponse
    {
        $result = $this->projectService->removeFile($request->user(), $project, $file);

        if (!$result) {
            return $this->error('File not found or unauthorized', 404);
        }

        return $this->success(null, 'File removed');
    }
}
