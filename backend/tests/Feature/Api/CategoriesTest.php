<?php

namespace Tests\Feature\Api;

use App\Models\JobCategory;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories(): void
    {
        JobCategory::factory()->create();

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_can_get_category_skills(): void
    {
        $category = JobCategory::factory()->create();
        Skill::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson("/api/categories/{$category->id}/skills");

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_returns_404_for_invalid_category_skills(): void
    {
        $response = $this->getJson('/api/categories/invalid-id/skills');

        $response->assertStatus(404);
    }
}
