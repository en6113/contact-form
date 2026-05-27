<?php

namespace Tests\Unit\Requests\Api\V1;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    private function validator(array $data)
    {
        $request = new IndexContactRequest;

        return Validator::make($data, $request->rules(), $request->messages());
    }

    /** @test */
    public function キーワード・性別・カテゴリ・日付・per_pageフィルタが有効である(): void
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $validator = $this->validator([
            'keyword' => 'Yamada',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2024-02-01',
            'per_page' => 50,
        ]);

        // Assert
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 検索フィルタを何もかけていない場合もバリデーションを通過できる(): void
    {
        // Act
        $validator = $this->validator([]);

        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function 性別フィルタに0が入力された場合はバリデーションエラーになる(): void
    {
        // Act
        $validator = $this->validator([
            'gender' => 0,
        ]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->messages());
    }

    /** @test */
    public function 性別フィルタに不正な値が指定された場合はバリデーションエラーになる(): void
    {
        // Act
        $validator = $this->validator([
            'gender' => 999,
        ]);

        // Assert
        $this->assertTrue($validator->fails());
    }

    /** @test */
    public function カテゴリフィルタに不正な値が指定された場合はバリデーションエラーになる(): void
    {
        // Arrange
        $nonExistentId = Category::max('id') + 1;

        // Act
        $validator = $this->validator([
            'category_id' => $nonExistentId,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->messages());
    }

    /** @test */
    public function 日付フィルタに不正な値が指定された場合はバリデーションエラーになる(): void
    {
        // Act
        $validator = $this->validator([
            'date' => 'not-a-date',
        ]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date', $validator->errors()->messages());
    }

    /** @test */
    public function ページフィルタに不正な値が指定された場合はバリデーションエラーになる(): void
    {
        // Act
        $validator = $this->validator([
            'per_page' => -1,
        ]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('per_page', $validator->errors()->messages());
    }

    /** @test */
    public function ページフィルタに0が入力された場合はバリデーションエラーになる(): void
    {
        // Act
        $validator = $this->validator([
            'per_page' => 0,
        ]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('per_page', $validator->errors()->messages());
    }
}
