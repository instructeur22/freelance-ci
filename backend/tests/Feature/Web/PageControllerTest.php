<?php

namespace Tests\Feature\Web;

use Tests\TestCase;

class PageControllerTest extends TestCase
{
    public function test_about_page_returns_200(): void
    {
        $response = $this->get('/about');

        $response->assertStatus(200);
    }

    public function test_terms_page_returns_200(): void
    {
        $response = $this->get('/terms');

        $response->assertStatus(200);
    }

    public function test_privacy_page_returns_200(): void
    {
        $response = $this->get('/privacy');

        $response->assertStatus(200);
    }

    public function test_contact_page_returns_200(): void
    {
        $response = $this->get('/contact');

        $response->assertStatus(200);
    }
}
