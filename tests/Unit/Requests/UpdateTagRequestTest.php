<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 【共通メソッド】
     * 偽装したFormRequestからバリデータを作成する
     */
    private function createValidator(array $data, Tag $targetTag): \Illuminate\Validation\Validator
    {
        $request = new class($targetTag) extends UpdateTagRequest
        {
            // 匿名クラスのコンストラクタ（初期化関数）$targetTagを$boundTagとして自動保存
            public function __construct(private Tag $boundTag) {}

            // UpdateTagRequestのroute() メソッドを上書き（オーバーライド）
            public function route($param = null, $default = null)
            {
                // FormRequest の中で $this->route('tag') と呼ばれたら、$this->boundTag($target）を返す
                if ($param === 'tag') {
                    return $this->boundTag;
                }

                return $default;
            }
        };

        return Validator::make($data, $request->rules());
    }

    /**  @test */
    public function タグ更新時にタグ名を維持することができる(): void
    {
        // Arrange
        $existTag = Tag::factory()->create(['name' => '存在するタグ名']);
        $targetTag = Tag::factory()->create(['name' => '更新前のタグ名']);

        // Act
        $validator = $this->createValidator(['name' => '更新前のタグ名'], $targetTag);

        // Assert
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function タグ更新時に他で既に使用されているタグ名への変更はバリデーションエラーになる(): void
    {
        // Arrange
        $existTag = Tag::factory()->create(['name' => '存在するタグ名']);
        $targetTag = Tag::factory()->create(['name' => '更新前のタグ名']);

        // Act
        $validator = $this->createValidator(['name' => '存在するタグ名'], $targetTag);

        // Assert
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->messages());
    }
}
