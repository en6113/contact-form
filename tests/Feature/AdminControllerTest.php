<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 認証されたユーザーのみが管理ダッシュボードを表示できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function 未認証ユーザーはログイン画面にリダイレクトされる(): void
    {
        $response = $this->get(route('admin.index'));

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 管理画面一覧でキーワードフィルタが機能し、結果が7件ごとにページネーションされる(): void
    {
        // Arrange
        $user = User::factory()->create();

        $targetContact = Contact::factory()->count(8)->create([
            'last_name' => '山田',
        ]);

        $otherContact = Contact::factory()->create([
            'last_name' => '佐藤',
        ]);

        // Act & Assert(1ページ目の検証)
        $responsePage1 = $this->actingAs($user)->get(route('admin.index', [
            'keyword' => '山田',
            'page' => 1
        ]));

        $responsePage1->assertStatus(200);
        $responsePage1->assertViewHas('contacts', function ($contacts) use ($otherContact) {
            return $contacts->count() === 7 && !$contacts->contains($otherContact);
        });

        // Act & Assert (2ページ目の検証)
        $responsePage2 = $this->actingAs($user)->get(route('admin.index', [
            'keyword' => '山田',
            'page' => 2
        ]));

        $responsePage2->assertStatus(200);
        $responsePage2->assertViewHas('contacts', function ($contacts) use ($otherContact) {
            return $contacts->count() === 1 && !$contacts->contains($otherContact);
        });
    }

    /** @test */
    public function 管理画面一覧で性別フィルタが機能し、結果が7件ごとにページネーションされる(): void
    {
        // Arrange
        $user = User::factory()->create();

        $maleContact = Contact::factory()->count(8)->create(['gender' => '1']);
        $femaleContact = Contact::factory()->create(['gender' => '2']);
        $otherContact = Contact::factory()->create(['gender' => '3']);

        // Act & Assert(1ページ目の検証)
        $responsePage1 = $this->actingAs($user)->get(route('admin.index', [
            'gender' => '1',
            'page' => 1
        ]));

        $responsePage1->assertStatus(200);
        $responsePage1->assertViewHas('contacts', function ($contacts) use ($femaleContact, $otherContact) {
            return $contacts->count() === 7
                && !$contacts->contains($femaleContact)
                && !$contacts->contains($otherContact);
        });

        // Act & Assert (2ページ目の検証)
        $responsePage2 = $this->actingAs($user)->get(route('admin.index', [
            'gender' => '1',
            'page' => 2
        ]));

        $responsePage2->assertStatus(200);
        $responsePage2->assertViewHas('contacts', function ($contacts) use ($femaleContact, $otherContact) {
            return $contacts->count() === 1
                && !$contacts->contains($femaleContact)
                && !$contacts->contains($otherContact);
        });
    }

    /** @test */
    public function 管理画面一覧でカテゴリーフィルタが機能し、結果が7件ごとにページネーションされる(): void
    {
        // Arrange
        $user = User::factory()->create();

        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $contactA = Contact::factory()->count(8)->create(['category_id' => $categoryA->id]);
        $contactB = Contact::factory()->create(['category_id' => $categoryB->id]);

        // Act & Assert(1ページ目の検証)
        $responsePage1 = $this->actingAs($user)->get(route('admin.index', [
            'category_id' => $categoryA->id,
            'page' => 1
        ]));

        $responsePage1->assertStatus(200);
        $responsePage1->assertViewHas('contacts', function ($contacts) use ($contactB) {
            return $contacts->count() === 7 && !$contacts->contains($contactB);
        });

        // Act & Assert (2ページ目の検証)
        $responsePage2 = $this->actingAs($user)->get(route('admin.index', [
            'category_id' => $categoryA->id,
            'page' => 2
        ]));

        $responsePage2->assertStatus(200);
        $responsePage2->assertViewHas('contacts', function ($contacts) use ($contactB) {
            return $contacts->count() === 1 && !$contacts->contains($contactB);
        });
    }

    /** @test */
    public function 管理画面一覧で日付フィルタが機能し、結果が7件ごとにページネーションされる(): void
    {
        // Arrange
        $user = User::factory()->create();

        $targetContact = Contact::factory()->count(8)->create([
            'created_at' => '2026-05-20 10:00:00',
        ]);

        $otherContact = Contact::factory()->create([
            'created_at' => '2026-04-20 10:00:00',
        ]);

        // Act & Assert(1ページ目の検証)
        $responsePage1 = $this->actingAs($user)->get(route('admin.index', [
            'date' => '2026-05-20',
            'page' => 1
        ]));

        $responsePage1->assertStatus(200);
        $responsePage1->assertViewHas('contacts', function ($contacts) use ($otherContact) {
            return $contacts->count() === 7 && !$contacts->contains($otherContact);
        });

        // Act & Assert (2ページ目の検証)
        $responsePage2 = $this->actingAs($user)->get(route('admin.index', [
            'date' => '2026-05-20',
            'page' => 2
        ]));

        $responsePage2->assertStatus(200);
        $responsePage2->assertViewHas('contacts', function ($contacts) use ($otherContact) {
            return $contacts->count() === 1 && !$contacts->contains($otherContact);
        });
    }

    /** @test */
    public function ユーザーは詳細ページ（カテゴリ情報付き）を表示することができる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create(['content' => 'カテゴリ名']);
        $contact = Contact::factory()->create([
            'category_id' => $category->id
        ]);

        $response = $this->actingAs($user)->get(route('admin.show', $contact));

        $response->assertStatus(200);
        $response->assertViewHas('contact');
        $response->assertSee('カテゴリ名');
    }

    /** @test */
    public function ユーザーはお問い合わせを削除できる(): void
    {
        $user = User::factory()->create();
        $contact = Contact::factory()->create([
            'email' => 'destroy@example.com'
        ]);

        $response = $this->actingAs($user)->delete(route('admin.destroy', $contact));

        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseMissing('contacts', ['email' => $contact->email]);
    }
}
