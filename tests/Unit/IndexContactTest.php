<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function キーワードフィルタが有効である(): void
    {
        // Arrange
        $user = User::factory()->create();

        $targetContact = Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'yamada@example.com',
        ]);

        $otherContact = Contact::factory()->create([
            'first_name' => '次郎',
            'last_name' => '佐藤',
            'email' => 'sato@example.com',
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('admin.index', [
            'keyword' => '山田',
        ]));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) use ($targetContact, $otherContact) {
            return $contacts->contains($targetContact) && ! $contacts->contains($otherContact);
        });
    }

    /** @test */
    public function 性別フィルタが有効である(): void
    {
        // Arrange
        $user = User::factory()->create();

        $maleContact = Contact::factory()->create(['gender' => '1']);
        $femaleContact = Contact::factory()->create(['gender' => '2']);
        $otherContact = Contact::factory()->create(['gender' => '3']);

        // Act
        $response = $this->actingAs($user)->get(route('admin.index', [
            'gender' => '1', // 男性
        ]));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) use ($maleContact, $femaleContact, $otherContact) {
            return $contacts->contains($maleContact)
                && ! $contacts->contains($femaleContact)
                && ! $contacts->contains($otherContact);
        });
    }

    /** @test */
    public function 性別フィルタで0が指定された場合は全件表示される(): void
    {
        // Arrange
        $user = User::factory()->create();

        $maleContact = Contact::factory()->create(['gender' => '1']);
        $femaleContact = Contact::factory()->create(['gender' => '2']);
        $otherContact = Contact::factory()->create(['gender' => '3']);

        // Act
        $response = $this->actingAs($user)->get(route('admin.index', [
            'gender' => '0',
        ]));

        // Assert
        $response->assertViewHas('contacts', function ($contacts) use ($maleContact, $femaleContact, $otherContact) {
            return $contacts->contains($maleContact)
                && $contacts->contains($femaleContact)
                && $contacts->contains($otherContact);
        });
    }

    /** @test */
    public function カテゴリーフィルタが有効である(): void
    {
        // Arrange
        $user = User::factory()->create();

        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $contactA = Contact::factory()->create(['category_id' => $categoryA->id]);
        $contactB = Contact::factory()->create(['category_id' => $categoryB->id]);

        // Act
        $response = $this->actingAs($user)->get(route('admin.index', [
            'category_id' => $categoryA->id,
        ]));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) use ($contactA, $contactB) {
            return $contacts->contains($contactA) && ! $contacts->contains($contactB);
        });
    }

    /** @test */
    public function 日付フィルタが有効である(): void
    {
        // Arrange
        $user = User::factory()->create();

        $targetContact = Contact::factory()->create([
            'created_at' => '2026-05-20 10:00:00',
        ]);

        $otherContact = Contact::factory()->create([
            'created_at' => '2026-04-20 10:00:00',
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('admin.index', [
            'date' => '2026-05-20',
        ]));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('contacts', function ($contacts) use ($targetContact, $otherContact) {
            return $contacts->contains($targetContact) && ! $contacts->contains($otherContact);
        });
    }
}
