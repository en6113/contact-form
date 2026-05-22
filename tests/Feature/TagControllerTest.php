<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Tag;

class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ユーザーはタグを作成できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('tag.store'), [
            'name' => 'テストタグ',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->AssertDatabaseHas('tags', [
            'name' => 'テストタグ',
        ]);
    }

    /** @test */
    public function ユーザーは編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->actingAs($user)->get(route('tag.edit', $tag));

        $response->assertStatus(200);
    }

    /** @test */
    public function ユーザーはタグを更新できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create([
            'name' => '更新前のタグ',
        ]);

        $response = $this->actingAs($user)->put(route('tag.update',$tag), [
            'name' => '更新後のタグ',
        ]);

        $response->assertRedirect(route('admin.index'));
        $this->AssertDatabaseHas('tags', [
            'name' => '更新後のタグ',
        ]);
    }

    /** @test */
    public function ユーザーはタグを削除できる(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create([
            'name' => '削除するタグ',
        ]);

        $response = $this->actingAs($user)->delete(route('tag.destroy',$tag));

        $response->assertRedirect(route('admin.index'));
        $this->AssertDatabaseMissing('tags', [
            'name' => '削除するタグ',
        ]);
    }

    /** @test */
    public function 未認証ユーザーがタグを作成しようとするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->post(route('tag.store'));

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 未認証ユーザーがタグ編集画面にアクセスするとログイン画面にリダイレクトされる(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->get(route('tag.edit', $tag));

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 未認証ユーザーがタグを更新しようとするとログイン画面にリダイレクトされる(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->put(route('tag.update', $tag), []);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function 未認証ユーザーがタグを削除しようとするとログイン画面にリダイレクトされる(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->delete(route('tag.destroy', $tag), []);

        $response->assertRedirect('/login');
    }
}
