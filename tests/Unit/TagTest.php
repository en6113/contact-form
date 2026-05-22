<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Tag;
use App\Models\Category;
use App\Models\Contact;

class TagTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function タグ名が空だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tag.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function タグ名は50文字まで入力できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tag.store'), [
            'name' => str_repeat('あ', 50),
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseHas('tags', [
            'name' => str_repeat('あ', 50),
        ]);
    }

    /** @test */
    public function タグ名が51文字以上だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tag.store'), [
            'name' => str_repeat('あ', 51),
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function タグ名が重複しているとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        Tag::factory()->create([
            'name' => '重複したタグ名',
        ]);

        $response = $this->actingAs($user)->post(route('tag.store'), [
            'name' => '重複したタグ名',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function タグ更新時にタグ名が重複しているとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        Tag::factory()->create(['name' => '重複するタグ名']);
        $tag = Tag::factory()->create(['name' => '更新前のタグ名']);

        $response = $this->actingAs($user)->put(route('tag.update',$tag), [
            'name' => '重複するタグ名',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function タグ更新時に自身の名前は維持できる(): void
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create([
            'name' => '更新前のタグ名',
        ]);

        $response = $this->actingAs($user)->put(route('tag.update', $tag), [
            'name' => '更新前のタグ名',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '更新前のタグ名',
        ]);
    }

    /** @test */
    public function 中間テーブルを介して1つのタグが複数のお問い合わせに紐づいている(): void
    {
        //Arrange
        $tag = Tag::factory()->create(['name' => '1つのタグ']);
        $category = Category::factory()->create();

        $contactA = Contact::factory()->create([
            'category_id' => $category->id,
            'detail' => '複数のお問い合わせ1'
        ]);
        $contactB = Contact::factory()->create([
            'category_id' => $category->id,
            'detail' => '複数のお問い合わせ2'
        ]);

        //Act
        $tag->contacts()->attach([$contactA->id, $contactB->id]);

        //Assert
        $this->assertCount(2, $tag->contacts);
        $this->assertTrue($tag->contacts->contains($contactA));
        $this->assertTrue($tag->contacts->contains($contactB));
    }
}
