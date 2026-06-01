<?php

namespace Tests\Feature\Api;

use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\JobCategory;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PublicEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_freelances(): void
    {
        $user = new User();
        $user->id = (string) Str::uuid();
        $user->role = UserRole::Freelance;
        $user->first_name = "John";
        $user->last_name = "Doe";
        $user->email = "john@example.com";
        $user->password = bcrypt("password");
        $user->save();

        $response = $this->getJson("/api/freelances");

        $response->assertStatus(200)
            ->assertJsonStructure(["data"]);
    }

    public function test_can_list_projects(): void
    {
        $client = new User();
        $client->id = (string) Str::uuid();
        $client->role = UserRole::Client;
        $client->email = "client@example.com";
        $client->password = bcrypt("password");
        $client->save();

        $category = new JobCategory();
        $category->id = (string) Str::uuid();
        $category->slug = "web-dev";
        $category->name = "Développement Web";
        $category->is_active = true;
        $category->save();

        $project = new Project();
        $project->id = (string) Str::uuid();
        $project->client_id = $client->id;
        $project->category_id = $category->id;
        $project->title = "Projet test";
        $project->description = "Description";
        $project->status = ProjectStatus::Open;
        $project->budget_min = 500;
        $project->budget_max = 1000;
        $project->currency = "XOF";
        $project->published_at = now();
        $project->save();

        $response = $this->getJson("/api/projects");

        $response->assertStatus(200)
            ->assertJsonStructure(["data"]);
    }
}
