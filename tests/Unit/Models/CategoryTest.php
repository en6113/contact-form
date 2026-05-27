<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function １つのカテゴリから、紐づく複数のお問い合わせ（has_many）を正しく取得できる(): void
    {
        $category = Category::factory()->create(['content' => 'カテゴリ']);
        $contacts = Contact::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);

        // 検証：このカテゴリに紐づくお問い合わせは3つであり、それがさっき作ったカテゴリであること
        $this->assertCount(3, $category->contacts);
        $this->assertTrue($category->contacts->contains($contacts->first()));
    }
}
