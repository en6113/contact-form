<?php

namespace Tests\Unit\Models;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 中間テーブルを介して1つのタグが複数のお問い合わせに紐づいている(): void
    {
        // Arrange
        $tag = Tag::factory()->create();
        $contacts = Contact::factory()->count(2)->create();

        // Act
        $tag->contacts()->attach($contacts->pluck('id'))->toArray();
        $tag->load('contacts');

        // Assert
        $this->assertCount(2, $tag->contacts);
        $this->assertTrue($tag->contacts->pluck('id')->contains($contacts->first()->id));
    }
}
