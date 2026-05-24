<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Contact;
use App\Models\Tag;

class ApiStoreContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 問い合わせの全ての必須項目の入力を受け付けることができる(): void
    {
        $contact = Contact::factory()->make();

        $response = $this->postJson(route('contacts.store'), [
            'category_id' => $contact->category_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertStatus(201);
        //IDが分からないためdata.idに何かしらの数字（ID）が入っていることを確認
        $response->assertJsonPath('data.id', 1);
    }

    /** @test */
    public function タグ入力を受け付けることができる(): void
    {
        //Arrange
        $contact = Contact::factory()->make();
        $tag = Tag::factory()->create();

        //Act
        $response = $this->postJson(route('contacts.store'), [
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $contact->category_id,
            'tag_ids' => [$tag->id],
            'detail' => $contact->detail
        ]);

        //DBに保存されたcontactをメールアドレスを頼りに1件取り出す
        $savedContact = Contact::where('email', $contact->email)->first();

        //Assert
        $response->assertStatus(201);
        // tags配列の中に作成したタグの情報が含まれているかを検証
        $response->assertJsonFragment([
            'id' => $tag->id,
            'name' => $tag->name
        ]);
    }

    /**
     * @test
     * @dataProvider invalidTelProvider
     */
    public function 不正な電話番号形式は拒否する(string $invalidTel): void
    {
        $contact = Contact::factory()->make();

        $response = $this->postJson(route('contacts.store'), [
            'category_id' => $contact->category_id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $invalidTel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertStatus(422);
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
            '文字が含まれる' => ['0901234567a']
        ];
    }
}