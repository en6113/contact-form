<?php

namespace Tests\Unit\Requests\Api\V1;

use App\Http\Requests\Api\V1\StoreContactRequest;
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

    private function validData(array $overrides = []): array
    {
        $category = Category::factory()->create();

        return array_merge([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => '渋谷ビル301',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
        ], $overrides);
    }

    /** @test */
    public function 問い合わせの全ての必須項目の入力を受け付けることができる(): void
    {
        // Act
        $validator = $this->validator($this->validData());

        // Assert
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function タグ入力を受け付けることができる(): void
    {
        // Arrange
        $tags = Tag::factory()->count(2)->create();

        // Act
        $validator = $this->validator(array_merge($this->validData(), [
            'tag_ids' => $tags->pluck('id')->toArray(),
        ]));

        // Assert
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 不正な値を入力するとバリデーションエラーになる(): void
    {
        // Act
        $validator = $this->validator([]);

        // Assert
        $this->assertTrue($validator->fails());
        $errors = $validator->errors()->messages();
        $this->assertArrayHasKey('first_name', $errors);
        $this->assertArrayHasKey('last_name', $errors);
        $this->assertArrayHasKey('gender', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('tel', $errors);
        $this->assertArrayHasKey('address', $errors);
        $this->assertArrayHasKey('category_id', $errors);
        $this->assertArrayHasKey('detail', $errors);
    }

    /** @test */
    public function 不正なメール形式は拒否する(): void
    {
        // Act
        $validator = $this->validator([
            'email' => 'invalid-email',
        ]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->messages());
    }

    /**
     * @test
     *
     * @dataProvider invalidTelProvider
     */
    public function 不正な電話番号形式は拒否する(string $invalidTel): void
    {
        $validator = $this->validator([
            'tel' => $invalidTel,
        ]);

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

    /** @test */
    public function 問い合わせ内容が120文字以上の場合はバリデーションエラーになる(): void
    {
        // Act
        $validator = $this->validator([
            'detail' => str_repeat('あ', 121),
        ]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('detail', $validator->errors()->messages());
    }

    /** @test */
    public function 性別値に０が入力された場合はバリデーションエラーになる(): void
    {
        // Act
        $validator = $this->validator([
            'gender' => 0,
        ]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->messages());
    }
}
