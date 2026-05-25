<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 問い合わせの全ての必須項目の入力を受け付けることができる(): void
    {
        $contact = Contact::factory()->make();

        $response = $this->post(route('contact.store'), [
            'category_id' => $contact->category_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $this->assertDatabaseHas('contacts', [
            'category_id' => $contact->category_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);
    }

    /** @test */
    public function タグ入力を受け付けることができる(): void
    {
        // Arrange
        $contact = Contact::factory()->make();
        $tag = Tag::factory()->create();

        // Act
        $response = $this->post(route('contact.store'), [
            'tag_ids' => [$tag->id],
            'category_id' => $contact->category_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        // DBに保存されたcontactをメールアドレスを頼りに1件取り出す
        $savedContact = Contact::where('email', $contact->email)->first();

        // Assert
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $savedContact->id,
            'tag_id' => $tag->id,
        ]);
    }

    /**
     * @test
     *
     * @dataProvider invalidTelProvider
     */
    public function 不正な電話番号形式は拒否する(string $invalidTel): void
    {
        $contact = Contact::factory()->make();

        $response = $this->post(route('contact.store'), [
            'category_id' => $contact->category_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $invalidTel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertSessionHasErrors(['tel']);
    }

    /**
     * 不正な電話番号のパターンを定義するデータプロバイダ
     */
    public static function invalidTelProvider(): array
    {
        return [
            'ハイフンが入っている' => ['090-1234-5678'],
            '桁数が足りない（10桁未満）' => ['09012345'],
            '桁数が多い（12桁以上）' => ['090123456789'],
            '全角数字が含まれる' => ['０９０12345678'],
            '文字が含まれる' => ['0901234567a'],
        ];
    }

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
