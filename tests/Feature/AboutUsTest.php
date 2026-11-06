<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutUsTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_us_page_loads_successfully(): void
    {
        $response = $this->get(route('about'));

        $response->assertStatus(200);
        $response->assertSee('About Us');
        $response->assertSee('100% Trusted Organic Food Store');
        // Ensure core value cards are not animated by GSAP to prevent opacity: 0 freeze
        $response->assertDontSee("gsap.from('.about-value-card'");
    }
}
