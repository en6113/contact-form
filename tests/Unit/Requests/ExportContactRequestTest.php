<?php

namespace Tests\Unit\Request;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    // 偽装したFormRequestからバリデータを作成する
    private function makeValidator(array $data)
    {
        $request = new ExportContactRequest;

        // 実際のFormRequestに定義されている rules() メソッドを使ってバリデータを作る
        return Validator::make($data, $request->rules());
    }

    /** @test */
    public function 正しいフィルタ条件であればバリデーションを通過する(): void
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $validator = $this->makeValidator([
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-05-24',
        ]);

        // Assert
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function キーワードが255文字以下であればバリデーションを通過する(): void
    {
        // Act
        $validator = $this->makeValidator([
            'keyword' => str_repeat('あ', 255),
        ]);

        // Assert
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function キーワードが256文字以上の場合はバリデーションエラーになる(): void
    {
        // Act
        $validator = $this->makeValidator([
            'keyword' => str_repeat('あ', 256),
        ]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('keyword', $validator->errors()->messages());
    }

    /** @test */
    public function 不正な性別が入力されたらバリデーションエラーになる(): void
    {
        // Act
        $validator = $this->makeValidator([
            'gender' => 999,
        ]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->messages());
    }

    /** @test */
    public function 存在しないカテゴリ_i_dが入力されたらバリデーションエラーになる(): void
    {
        // Arrange
        $nonExistentId = Category::max('id') + 1;

        // Act
        $validator = $this->makeValidator([
            'category_id' => $nonExistentId,
        ]);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->messages());
    }
}
