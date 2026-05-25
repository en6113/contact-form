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
        $tags = Tag::all();

        $contacts = Contact::factory()->count(20)->create();

        // 各contactに対してランダムに1〜3個のタグを紐付ける(中間テーブルに保存)
        foreach ($contacts as $contact) {
            $randomTags = $tags->random(rand(1, 3));

            $contact->tags()->attach($randomTags);
        }
    }
}
