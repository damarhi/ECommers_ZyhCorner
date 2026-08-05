<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_page_loads_successfully()
    {
        $response = $this->get(route('faq'));
        $response->assertStatus(200);
        $response->assertSee('FAQ');
        $response->assertSee('Bagaimana cara melakukan pemesanan kue di Zyh Corner?');
    }
}
