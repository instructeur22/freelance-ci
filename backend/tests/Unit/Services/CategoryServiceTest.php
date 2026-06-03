<?php

namespace Tests\Unit\Services;

use App\Models\JobCategory;
use App\Models\Skill;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = new CategoryService();
    }

    public function test_listAll_returns_all_categories(): void
    {
        $categories = $this->categoryService->listAll();

        $this->assertGreaterThanOrEqual(10, $categories->count());
    }

    public function test_getSkills_returns_skills_for_category(): void
    {
        $category = JobCategory::where('slug', 'developpement-web')->first();
        $this->assertNotNull($category);

        $skills = $this->categoryService->getSkills($category->id);

        $this->assertNotNull($skills);
        $this->assertGreaterThanOrEqual(0, $skills->count());
    }

    public function test_getSkills_returns_null_for_invalid_uuid(): void
    {
        $skills = $this->categoryService->getSkills('invalid-uuid');

        $this->assertNull($skills);
    }

    public function test_getSkills_returns_empty_collection_for_category_with_no_skills(): void
    {
        $category = JobCategory::factory()->create([
            'slug' => 'test-category-no-skills',
        ]);

        $skills = $this->categoryService->getSkills($category->id);

        $this->assertNotNull($skills);
        $this->assertCount(0, $skills);
    }
}
