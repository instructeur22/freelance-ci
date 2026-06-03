<?php

namespace Tests\Unit\Services;

use App\Enums\ProjectStatus;
use App\Models\JobCategory;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProjectService $projectService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->projectService = new ProjectService();
    }

    public function test_list_returns_paginated_open_projects(): void
    {
        Project::factory()->count(3)->create();
        Project::factory()->closed()->count(2)->create();

        $projects = $this->projectService->list();

        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $projects);
        $this->assertEquals(3, $projects->total());
    }

    public function test_create_creates_project(): void
    {
        $client = User::factory()->client()->create();
        $category = JobCategory::factory()->create();

        $project = $this->projectService->create($client, [
            'title' => 'New project',
            'description' => 'Project description',
            'category_id' => $category->id,
            'budget_min' => 50000,
            'budget_max' => 100000,
            'currency' => 'XOF',
            'duration_days' => 30,
        ]);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals($client->id, $project->client_id);
        $this->assertEquals(ProjectStatus::Open, $project->status);
    }

    public function test_update_modifies_project(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id, 'title' => 'Old title']);

        $updated = $this->projectService->update($client, $project->id, [
            'title' => 'Updated title',
        ]);

        $this->assertNotNull($updated);
        $this->assertEquals('Updated title', $updated->title);
    }

    public function test_delete_removes_project(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        $result = $this->projectService->delete($client, $project->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted($project);
    }

    public function test_addFile_creates_file(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        $file = $this->projectService->addFile($client, $project->id, [
            'file_url' => 'https://example.com/doc.pdf',
            'file_name' => 'doc.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        $this->assertInstanceOf(ProjectFile::class, $file);
        $this->assertEquals($project->id, $file->project_id);
    }
}
