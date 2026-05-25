<?php

namespace Tests\Feature;

use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnauthenticatedRedirectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証ユーザーはお問い合わせ一覧にアクセスするとログインページにリダイレクトされる(): void
    {
        // Act
        $response = $this->get(route('admin.index'));

        // Assert
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーがお問い合わせをエクスポートしようとするとログインページにリダイレクトされる(): void
    {
        // Act
        $response = $this->get(route('contact.export'));

        // Assert
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーはタグを保存しようとするとログインページにリダイレクトされる()
    {
        // Act
        $response = $this->post(route('tag.store'), [
            'name' => '新規タグ',
        ]);

        // Assert
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーはお問い合わせを削除しようとするとログインページにリダイレクトされる()
    {
        // Arrange
        $contact = Contact::factory()->create();

        // Act
        $response = $this->delete(route('admin.destroy', $contact));

        // Assert
        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('contacts', ['id' => $contact->id]);
    }
}
