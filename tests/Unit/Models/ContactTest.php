<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function １つのお問い合わせが特定のカテゴリに属している(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertTrue($contact->category->is($category));
    }

    /** @test */
    public function １つのお問い合わせが複数のタグと同期（sync）している(): void
    {
        // Arrange
        $contact = Contact::factory()->create();

        $tagA = Tag::factory()->create(['name' => 'タグA']);
        $tagB = Tag::factory()->create(['name' => 'タグB']);
        $tagC = Tag::factory()->create(['name' => 'タグC']);

        // タグAと紐づいた既存データをつくり、事前に紐づけをチェック
        $contact->tags()->attach($tagA->id);
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tagA->id,
        ]);

        // Act タグB、タグCに変更して同期
        $contact->tags()->sync([$tagB->id, $tagC->id]);

        // Assert
        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tagA->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tagB->id,
        ]);
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tagC->id,
        ]);
    }
}
