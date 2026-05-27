<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    // バリデータを作る処理
    private function validator(array $data)
    {
        $request = new IndexContactRequest;

        return Validator::make($data, $request->rules(), $request->messages());
    }

    /** @test */
    public function キーワード・性別・カテゴリ・日付フィルタが有効である(): void
    {
        $category = Category::factory()->create();

        $validator = $this->validator([
            'keyword' => 'Yamada',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2025-05-20',
        ]);

        $this->assertTrue($validator->passes());

    }

    /** @test */
    public function 不正な性別値を拒否する(): void
    {
        $validator = $this->validator([
            'gender' => 999,
        ]);

        $this->assertTrue($validator->fails());
        // 発生したエラーメッセージの中にgenderに関するエラーが含まれていることを検証
        $this->assertArrayHasKey('gender', $validator->errors()->messages());
    }
}
