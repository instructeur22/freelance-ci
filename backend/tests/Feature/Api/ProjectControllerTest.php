<?php

namespace Tests\Feature\Api;

use App\Models\JobCategory;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_projects(): void
    {
        Project::factory()->count(3)->create();

        $response = $this->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_show_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->getJson('/api/projects/' . $project->id);

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_create_project(): void
    {
        $client = User::factory()->client()->create();
        $category = JobCategory::factory()->create();

        $response = $this->withHeaders($this->authHeaders($client))
            ->postJson('/api/projects', [
                'title' => 'New project',
                'description' => 'Project description',
                'category_id' => $category->id,
                'budget_min' => 50000,
                'budget_max' => 100000,
                'currency' => 'XOF',
                'duration_days' => 30,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data']);
    }

    public function test_can_update_project(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        $response = $this->withHeaders($this->authHeaders($client))
            ->putJson('/api/projects/' . $project->id, [
                'title' => 'Updated title',
            ]);

        $response->assertStatus(200);
    }

    public function test_can_delete_project(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        $response = $this->withHeaders($this->authHeaders($client))
            ->deleteJson('/api/projects/' . $project->id);

        $response->assertStatus(200);
        $this->assertSoftDeleted($project);
    }

    public function test_can_add_project_file(): void
    {
        $client = User::factory()->client()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        $response = $this->withHeaders($this->authHeaders($client))
            ->postJson('/api/projects/' . $project->id . '/files', [
                'file_url' => 'https://example.com/doc.pdf',
                'file_name' => 'doc.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 1024,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data']);
    }
}
