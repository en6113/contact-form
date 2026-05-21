<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Contact::factory()->count(20)->create();

        /* モデルでリレーション定義したら変更する
        $tags = Tag::all();

        Contact::factory()
            ->count(20)
            ->hasAttached($tags, function () {
                return ['count' => rand(1, 3)]; // 1〜3件をランダムに選ぶ設定
            })
            ->create();
        */
        }
}
