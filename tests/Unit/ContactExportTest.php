<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Requests\ExportContactRequest;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Category;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;
    /**
     * バリデーションを実行して結果を返す補助メソッド
     */
    private function validate(array $data): bool
    {
        $request = new ExportContactRequest();

        // 実際のFormRequestに定義されている rules() メソッドを使ってバリデータを作る
        $validator = Validator::make($data, $request->rules());

        // バリデーションが通れば true、エラーがあれば false を返す
        return $validator->passes();
    }

    /** @test */
    public function 正しいフィルタ条件であればバリデーションを通過する(): void
    {
        // Arrange
        $category = Category::factory()->create();

        // Act
        $result = $this->validate([
            'keyword' => 'テスト',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-05-24',
        ]);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function キーワードが255文字以下であればバリデーションを通過する(): void
    {
        // Act
        $result = $this->validate(['keyword' => str_repeat('あ', 255),]);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function キーワードが256文字以上の場合はバリデーションエラーになる(): void
    {
        // Act
        $result = $this->validate([
            'keyword' => str_repeat('あ', 256),
        ]);

        // Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function 不正な性別が入力されたらバリデーションエラーになる(): void
    {
        //Act
        $result = $this->validate(['gender' => 4,]);

        //Assert
        $this->assertFalse($result);
    }

    /** @test */
    public function 存在しないカテゴリIDが入力されたらバリデーションエラーになる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $nonExistentId = Category::max('id') + 1;

        // Act
        $response = $this->actingAs($user)->get(route('contact.export', ['category_id' => $nonExistentId]));

        // Assert
        $response->assertSessionHasErrors(['category_id']);
    }
}
