<?php

namespace Tests\Feature\Api;

use App\Models\JobCategory;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories(): void
    {
        $category = new JobCategory();
        $category->id = (string) Str::uuid();
        $category->slug = "web-dev";
        $category->name = "Développement Web";
        $category->is_active = true;
        $category->save();

        $response = $this->getJson("/api/categories");

        $response->assertStatus(200)
            ->assertJsonStructure(["data"]);
    }

    public function test_can_get_category_skills(): void
    {
        $category = new JobCategory();
        $category->id = (string) Str::uuid();
        $category->slug = "web-dev";
        $category->name = "Développement Web";
        $category->is_active = true;
        $category->save();

        $skill = new Skill();
        $skill->id = (string) Str::uuid();
        $skill->category_id = $category->id;
        $skill->name = "Laravel";
        $skill->slug = "laravel";
        $skill->save();

        $response = $this->getJson("/api/categories/{$category->id}/skills");

        $response->assertStatus(200)
            ->assertJsonStructure(["data"]);
    }

    public function test_returns_404_for_invalid_category_skills(): void
    {
        $response = $this->getJson("/api/categories/invalid-id/skills");

        $response->assertStatus(404);
    }
}
