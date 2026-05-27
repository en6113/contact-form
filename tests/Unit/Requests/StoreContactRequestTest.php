<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validator(array $data)
    {
        $request = new StoreContactRequest;

        return Validator::make($data, $request->rules(), $request->messages());
    }

    private function requiredData(Category $category, array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Hanako',
            'last_name' => 'Sato',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '0312345678',
            'address' => 'Tokyo',
            'building' => 'Skytree',
            'category_id' => $category->id,
            'detail' => 'テストお問い合わせ',
        ], $overrides);
    }

    /** @test */
    public function 問い合わせの全ての必須項目とタグ入力を受け付けることができる(): void
    {
        // Arrange
        $category = Category::factory()->create();
        $tags = Tag::factory()->create();

        // Act
        $validator = $this->validator($this->requiredData($category, [
            'tag_ids' => $tags->pluck('id')->toArray(),
        ]));

        // Assert
        $this->assertTrue($validator->passes());
    }

    /**
     * @test
     *
     * @dataProvider invalidTelProvider
     */
    public function 不正な電話番号形式は拒否する(string $invalidTel): void
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $validator = $this->validator($this->requiredData($category, [
            'tel' => $invalidTel,
        ]));

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tel', $validator->errors()->messages());
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
}
