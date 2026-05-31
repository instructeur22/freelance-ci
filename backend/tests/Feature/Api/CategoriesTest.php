<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class CategoriesTest extends TestCase
{
    public function test_can_list_categories(): void
    {
        $this->markTestSkipped('Requires PostgreSQL database with seeded data. Configure phpunit.xml with DB_CONNECTION=pgsql to run.');
    }

    public function test_can_get_category_skills(): void
    {
        $this->markTestSkipped('Requires PostgreSQL database with seeded data.');
    }

    public function test_returns_404_for_invalid_category_skills(): void
    {
        $this->markTestSkipped('Requires PostgreSQL database with seeded data.');
    }
}
