<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class PublicEndpointsTest extends TestCase
{
    public function test_can_list_freelances(): void
    {
        $this->markTestSkipped('Requires PostgreSQL database with seeded data. Configure phpunit.xml with DB_CONNECTION=pgsql to run.');
    }

    public function test_can_list_projects(): void
    {
        $this->markTestSkipped('Requires PostgreSQL database with seeded data.');
    }
}
