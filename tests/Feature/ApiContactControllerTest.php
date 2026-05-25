<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiContactControllerTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // お問い合わせ一覧API (GET /api/v1/contacts)
    // =========================================================================

    /** @test */
    public function お問い合わせ一覧を_jso_n形式で取得できる(): void
    {
        // Arrange
        Contact::factory()->count(20)->create();

        // Act
        $response = $this->getJson(route('contacts.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(20, 'data');
    }

    /** @test */
    public function お問い合わせ一覧でキーワード検索が機能し、結果がページネーションされる(): void
    {
        // Arrange
        $targetContact = Contact::factory()->count(21)->create(['last_name' => '山田']);
        $otherContact = Contact::factory()->create(['last_name' => '佐藤']);

        // Act$Assert(1ページ目の検証)
        $responsePage1 = $this->getJson(route('contacts.index', [
            'keyword' => '山田',
            'per_page' => 20,
            'page' => 1,
        ]));

        $responsePage1->assertStatus(200);
        $responsePage1->assertJsonCount(20, 'data');
        $responsePage1->assertJsonFragment(['id' => $targetContact->first()->id]);

        // Act$Assert(2ページ目の検証)
        $responsePage2 = $this->getJson(route('contacts.index', [
            'keyword' => '山田',
            'per_page' => 20,
            'page' => 2,
        ]));

        $responsePage2->assertStatus(200);
        $responsePage2->assertJsonCount(1, 'data');
        $responsePage2->assertJsonFragment(['id' => $targetContact->last()->id]);
    }

    /** @test */
    public function お問い合わせ一覧で性別検索が機能、結果がページネーションされる(): void
    {
        // Arrange
        $maleContact = Contact::factory()->count(21)->create(['gender' => '1']);
        $femaleContact = Contact::factory()->create(['gender' => '2']);
        $otherContact = Contact::factory()->create(['gender' => '3']);

        // Act$Assert(1ページ目の検証)
        $responsePage1 = $this->getJson(route('contacts.index', [
            'gender' => '1',
            'per_page' => 20,
            'page' => 1,
        ]));

        $responsePage1->assertStatus(200);
        $responsePage1->assertJsonCount(20, 'data');
        $responsePage1->assertJsonFragment(['id' => $maleContact->first()->id]);

        // Act$Assert(2ページ目の検証)
        $responsePage2 = $this->getJson(route('contacts.index', [
            'gender' => '1',
            'per_page' => 20,
            'page' => 2,
        ]));

        $responsePage2->assertStatus(200);
        $responsePage2->assertJsonCount(1, 'data');
        $responsePage2->assertJsonFragment(['id' => $maleContact->last()->id]);
    }

    /** @test */
    public function お問い合わせ一覧でカテゴリ検索が機能、結果がページネーションされる(): void
    {
        // Arrange
        $categoryA = Category::factory()->create();
        $categoryB = Category::factory()->create();

        $contact = Contact::factory()->count(21)->create(['category_id' => $categoryA->id]);
        $otherContact = Contact::factory()->create(['category_id' => $categoryB->id]);

        // Act$Assert(1ページ目の検証)
        $responsePage1 = $this->getJson(route('contacts.index', [
            'category_id' => $categoryA->id,
            'per_page' => 20,
            'page' => 1,
        ]));

        $responsePage1->assertStatus(200);
        $responsePage1->assertJsonCount(20, 'data');
        $responsePage1->assertJsonFragment(['id' => $contact->first()->id]);

        // Act$Assert(2ページ目の検証)
        $responsePage2 = $this->getJson(route('contacts.index', [
            'category_id' => $categoryA->id,
            'per_page' => 20,
            'page' => 2,
        ]));

        $responsePage2->assertStatus(200);
        $responsePage2->assertJsonCount(1, 'data');
        $responsePage2->assertJsonFragment(['id' => $contact->last()->id]);
    }

    /** @test */
    public function お問い合わせ一覧で日付検索が機能、結果がページネーションされる(): void
    {
        // Arrange
        $targetContact = Contact::factory()->count(21)->create(['created_at' => '2026-05-20 10:00:00']);
        $otherContact = Contact::factory()->create(['created_at' => '2026-04-20 10:00:00']);

        // Act & Assert(1ページ目の検証)
        $responsePage1 = $this->getJson(route('contacts.index', [
            'date' => '2026-05-20',
            'per_page' => 20,
            'page' => 1,
        ]));

        $responsePage1->assertStatus(200);
        $responsePage1->assertJsonCount(20, 'data');
        $responsePage1->assertJsonFragment(['id' => $targetContact->first()->id]);

        // Act & Assert(2ページ目の検証)
        $responsePage2 = $this->getJson(route('contacts.index', [
            'date' => '2026-05-20',
            'per_page' => 20,
            'page' => 2,
        ]));

        $responsePage2->assertStatus(200);
        $responsePage2->assertJsonCount(1, 'data');
        $responsePage2->assertJsonFragment(['id' => $targetContact->last()->id]);
    }

    /** @test */
    public function お問い合わせ一覧の検索機能でバリデーションエラー時は422が返る(): void
    {
        // Act
        $response = $this->getJson(route('contacts.index', ['gender' => '4']));

        // Assert
        $response->assertStatus(422);
    }

    // =========================================================================
    // お問い合わせ詳細API (GET /api/v1/contacts/{id})
    // =========================================================================

    /** @test */
    public function お問い合わせ詳細にアクセスすると_jso_n形式の詳細が返る(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact->tags()->attach($tag->id);

        // Act
        $response = $this->getJson(route('contacts.show', $contact));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $contact->id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'gender' => $contact->gender,
                'email' => $contact->email,
                'tel' => $contact->tel,
                'address' => $contact->address,
                'category' => [
                    'id' => $category->id,
                    'content' => $category->content,
                ],
                'tags' => [
                    [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ],
                ],
                'detail' => $contact->detail,
            ],
        ]);
    }

    /** @test */
    public function 存在しない_i_dでお問い合わせ詳細にアクセスすると404エラー_jso_nが返る(): void
    {
        // Arrange
        $nonExistentId = Contact::max('id') + 1;

        // Act
        $response = $this->getJson(route('contacts.show', $nonExistentId));

        // Assert
        $response->assertStatus(404);
        $response->assertJsonFragment(['message' => 'お問い合わせが見つかりませんでした。']);
    }

    // =========================================================================
    // お問い合わせ作成API (POST /api/v1/contacts)
    // =========================================================================

    /** @test */
    public function お問い合わせを作成するとレコードが作成され201が返る(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        $contactData = Contact::factory()->make(['category_id' => $category->id])->toArray();

        $contactData['tag_ids'] = [$tag->id];

        // Act
        $response = $this->postJson(route('contacts.store'), $contactData);

        $newContactId = $response->json('data.id'); // 新しく作られたContactのIDを取得

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('contacts', [
            'id' => $newContactId,
            'first_name' => $contactData['first_name'],
            'last_name' => $contactData['last_name'],
            'email' => $contactData['email'],
            'category_id' => $category->id,
            'detail' => $contactData['detail'],
        ]);
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $newContactId,
            'tag_id' => $tag->id,
        ]);
    }

    /** @test */
    public function お問い合わせ作成時にバリデーションエラーがある場合は422が返る(): void
    {
        // Act
        $response = $this->postJson(route('contacts.store'), ['email' => 'user_example.com']);

        $response->assertStatus(422);
    }

    /** @test */
    public function お問い合わせ作成時に定義外のパラメータが送られても無視される(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $validData = [
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都新宿区...',
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
            'detail' => 'お問い合わせ内容のテストです。',
        ];

        // 正しいデータに定義外のパラメータを混ぜる
        $requestData = array_merge($validData, [
            'spam' => 'spam_value',
        ]);

        // Act
        $response = $this->postJson(route('contacts.store'), $requestData);

        // Assert
        $response->assertStatus(201);
        $this->assertDatabaseHas('contacts', ['email' => 'test@example.com']);
        $this->assertDatabaseMissing('contacts', ['spam' => 'spam_value']);
    }

    // =========================================================================
    // お問い合わせ更新API (PUT /api/v1/contacts/{id})
    // =========================================================================

    /** @test */
    public function お問い合わせを更新するとレコードが更新され200が返る(): void
    {
        // Arrange
        $contact = Contact::factory()->create(['first_name' => '山田']);
        $updateData = $contact->toArray();
        $updateData['first_name'] = '鈴木';

        // Act
        $response = $this->putJson(route('contacts.update', $contact), $updateData);

        // Assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '鈴木',
        ]);
    }

    /** @test */
    public function 存在しない_i_dでお問い合わせを更新しようとすると404が返る(): void
    {
        // Arrange (絶対に存在しないIDを動的に生成)
        $nonExistentId = Contact::max('id') + 1;

        // Act
        $response = $this->putJson(route('contacts.update', $nonExistentId));

        // Assert
        $response->assertStatus(404);
    }

    /** @test */
    public function お問い合わせ更新時にバリデーションエラーがある場合は422が返る(): void
    {
        // Arrange
        $contact = Contact::factory()->create(['email' => 'user@example.com']);

        // Act
        $response = $this->putJson(route('contacts.update', $contact), ['email' => 'user_example.com']);

        // Assert
        $response->assertStatus(422);
        $this->assertDatabaseHas('contacts', ['email' => 'user@example.com']);
        $this->assertDatabaseMissing('contacts', ['email' => 'user_example.com']);
    }

    // =========================================================================
    // お問い合わせ削除API (DELETE /api/v1/contacts/{id})
    // =========================================================================

    /** @test */
    public function お問い合わせを削除すると204が返りレコードが削除される(): void
    {
        // Arrange
        $contact = Contact::factory()->create();

        // Act
        $response = $this->deleteJson(route('contacts.destroy', $contact));

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('contacts', ['id' => $contact->id]);
    }

    /** @test */
    public function 存在しない_i_dでお問い合わせを削除しようとすると404が返る(): void
    {
        // Arrange
        $nonExistentId = Contact::max('id') + 1;

        // Act
        $response = $this->deleteJson(route('contacts.destroy', $nonExistentId));

        // Assert
        $response->assertStatus(404);
    }
}
