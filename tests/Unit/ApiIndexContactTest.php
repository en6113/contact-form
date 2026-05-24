<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Contact;
use App\Models\Category;

class ApiIndexContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function キーワードフィルタが有効である(): void
    {
        // Arrange
        $targetContact = Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com'
        ]);

        $otherContact = Contact::factory()->create([
            'first_name' => '次郎',
            'last_name' => '佐藤',
            'email' => 'sato@example.com'
        ]);

        // Act
        $response = $this->getJson(route('contacts.index', ['keyword' => '山田']));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $targetContact->id]);
    }

    /** @test */
    public function 性別フィルタが有効である(): void
    {
        // Arrange
        $maleContact = Contact::factory()->create(['gender' => '1']);
        $femaleContact = Contact::factory()->create(['gender' => '2']);
        $otherContact = Contact::factory()->create(['gender' => '3']);

        // Act
        $response = $this->getJson(route('contacts.index', ['gender' => '1']));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $maleContact->id]);
    }

    /** @test */
    public function カテゴリーフィルタが有効である(): void
    {
        // Arrange
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $contactA = Contact::factory()->create(['category_id' => $categoryA->id]);
        $contactB = Contact::factory()->create(['category_id' => $categoryB->id]);

        // Act
        $response = $this->getJson(route('contacts.index', ['category_id' => $categoryA->id]));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $contactA->id]);
    }

    /** @test */
    public function 日付フィルタが有効である(): void
    {
        $targetContact = Contact::factory()->create(['created_at' => '2026-05-20 10:00:00',]);

        $otherContact = Contact::factory()->create(['created_at' => '2026-04-20 10:00:00',]);

        // Act
        $response = $this->getJson(route('contacts.index', ['date' => '2026-05-20']));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $targetContact->id]);
    }

    /** @test */
    public function ページフィルタが有効である(): void
    {
        // Arrange
        $content = Contact::factory()->count(8)->create();

        // Act & Assert(1ページ目の検証)
        $response = $this->getJson(route('contacts.index', [
            'page' => 1,
            'per_page' => 7
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(7, 'data');

        // Act & Assert(2ページ目の検証)
        $response = $this->getJson(route('contacts.index', [
            'page' => 2,
            'per_page' => 7
        ]));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /** @test */
    public function 性別フィルタに不正な値が指定された場合はバリデーションエラーになる(): void
    {
        $response = $this->getJson(route('contacts.index', ['gender' => '4']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['gender']);
    }

    /** @test */
    public function カテゴリフィルタに不正な値が指定された場合はバリデーションエラーになる(): void
    {
        $response = $this->getJson(route('contacts.index', ['category_id' => 99999]));
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category_id']);
    }

    /** @test */
    public function 日付フィルタに不正な値が指定された場合はバリデーションエラーになる(): void
    {
        $response = $this->getJson(route('contacts.index', ['date' => 'invalid-date-string']));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date']);
    }

    /** @test */
    public function ページフィルタに不正な値が指定された場合はバリデーションエラーになる(): void
    {
        $response = $this->getJson(route('contacts.index', ['per_page' => -1]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }
}
