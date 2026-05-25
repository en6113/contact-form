<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement([1, 2, 3]),
            'email' => fake()->email(),
            'tel' => fake()->numerify('090########'),
            'address' => fake()->address(),
            'detail' => fake()->randomElement([
                'お世話になります。先ほど注文した商品について確認したいことがありご連絡いたしました。',
                '配送日時の変更は可能でしょうか？できれば今週の土曜日の午前中を希望しております。',
                '商品に傷がありました。商品を交換していただくことは可能でしょうか？',
                '先月注文した商品がまだ届いていません。発送はいつ頃になりますでしょうか。',
                '商品のクオリティが非常に高く、満足しています。定期配送を依頼することは可能でしょうか。',
            ]),
        ];
    }
}
