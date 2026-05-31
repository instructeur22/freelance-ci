<?php

namespace Tests\Unit\Services;

use App\Services\CategoryService;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;

class CategoryServiceTest extends TestCase
{
    private CategoryService $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = new CategoryService();
    }

    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(CategoryService::class, $this->categoryService);
    }

    public function test_getSkills_returns_null_for_invalid_uuid(): void
    {
        $this->markTestSkipped('Requires database with job_categories table');
    }
}
