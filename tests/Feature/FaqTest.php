<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Faq;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_page_loads_successfully()
    {
        Faq::create([
            'pertanyaan' => 'Apa itu Zyh Corner?',
            'jawaban' => 'Zyh Corner adalah toko pastry & bakery pilihan terbaik.',
            'kategori' => 'Umum',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $response = $this->get(route('faq'));
        $response->assertStatus(200);
        $response->assertSee('Apa itu Zyh Corner?');
    }
}
