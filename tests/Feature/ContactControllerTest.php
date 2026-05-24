<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;

class ContactControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function お問い合わせフォーム入力ページが正常に表示され、categories・tagsがビュー変数として渡される(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('categories');
        $response->assertViewHas('tags');
    }

    /** @test */
    public function カテゴリ名、タグ名がページに表示される(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee($category->content);
        $response->assertSee($tag->name);
    }

    /** @test */
    public function サンクスページが正常に表示される(): void
    {
        $response = $this->get(route('contact.thanks'));

        $response->assertStatus(200);
    }


    /** @test */
    public function バリデーション通過後、お問い合わせフォーム確認ページが表示される(): void
    {
        //Arrange
        $contact = Contact::factory()->create();
        $data = [
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'category_id' => $contact->category_id,
            'detail' => $contact->detail,
        ];

        //Act
        $response = $this->post(route('contact.confirm'), $data);

        //Assert
        $response->assertStatus(200);
    }

    /** @test */
    public function お問い合わせフォーム確認ページに入力内容が表示される(): void
    {
        //Arrange
        $category = Category::factory()->create(['content' => 'カテゴリ名']);
        $tag = Tag::factory()->create(['name' => 'タグ名']);
        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => '1', //男性
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
            'detail' => 'お問い合わせのテスト内容です。',
        ];

        //Act
        $response = $this->post(route('contact.confirm'), $data);

        //Assert
        $response->assertStatus(200);
        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('男性');
        $response->assertSee('test@example.com');
        $response->assertSee('09012345678');
        $response->assertSee('東京都渋谷区');
        $response->assertSee($category->content);
        $response->assertSee($tag->name);
        $response->assertSee('お問い合わせのテスト内容です。');
    }

    /** @test */
    public function 確認時に、カテゴリーが未選択だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定（そのままだとリダイレクト先がホーム画面になってしまうため）
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.confirm'), [
            'category_id' => '',
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['category_id']);
    }

    /** @test */
    public function 確認時に、姓（苗字）が未選択だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.confirm'), [
            'category_id' => $contact->category->id,
            'first_name' => '',
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['first_name']);
    }

    /** @test */
    public function 確認時に、名前が未入力だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.confirm'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => '',
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['last_name']);
    }

    /** @test */
    public function 確認時に、性別が未選択だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.confirm'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => '',
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['gender']);
    }

    /** @test */
    public function 確認時に、メールアドレスが未入力だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.confirm'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => '',
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function 確認時に、電話番号が未入力だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.confirm'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => '',
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['tel']);
    }

    /** @test */
    public function 確認時に、住所が未入力だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.confirm'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => '',
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['address']);
    }

    /** @test */
    public function 確認時に、お問い合わせ内容が未入力だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.confirm'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => '',
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['detail']);
    }

    /** @test */
    public function 送信後はお問い合わせ内容が保存され、サンクス画面へリダイレクトされる(): void
    {
        //Arrange
        $category = Category::factory()->create(['content' => 'カテゴリ名']);
        $tag = Tag::factory()->create(['name' => 'タグ名']);
        $data = [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => '1', //男性
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'category_id' => $category->id,
            'tag_ids' => [$tag->id],
            'detail' => 'お問い合わせのテスト内容です。',
        ];

        //Act
        $response = $this->post(route('contact.store'), $data);

        //Assert
        $response->assertRedirect(route('contact.thanks'));
        $this->assertDatabaseHas('contacts', ['first_name' => '太郎']);
        $this->assertDatabaseHas('contacts', ['last_name' => '山田']);
        $this->assertDatabaseHas('contacts', ['gender' => '1']);
        $this->assertDatabaseHas('contacts', ['email' =>'test@example.com']);
        $this->assertDatabaseHas('contacts', ['tel' => '09012345678']);
        $this->assertDatabaseHas('contacts', ['address' => '東京都渋谷区']);
        $this->assertDatabaseHas('contacts', ['category_id' => $category->id]);
        $this->assertDatabaseHas('contact_tag', ['tag_id' => $tag->id]);
        $this->assertDatabaseHas('contacts', ['detail' => 'お問い合わせのテスト内容です。']);
    }

    /** @test */
    public function 送信時に、カテゴリーが未選択だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定（そのままだとリダイレクト先がホーム画面になってしまうため）
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.store'), [
            'category_id' => '',
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['category_id']);
    }

    /** @test */
    public function 送信時に、姓（苗字）が未選択だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.store'), [
            'category_id' => $contact->category->id,
            'first_name' => '',
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['first_name']);
    }

    /** @test */
    public function 送信時に、名前が未入力だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.store'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => '',
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['last_name']);
    }

    /** @test */
    public function 送信時に、性別が未選択だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.store'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => '',
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['gender']);
    }

    /** @test */
    public function 送信時に、メールアドレスが未入力だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.store'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => '',
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function 送信時に、電話番号が未入力だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.store'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => '',
            'address' => $contact->address,
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['tel']);
    }

    /** @test */
    public function 送信時に、住所が未入力だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.store'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => '',
            'detail' => $contact->detail,
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['address']);
    }

    /** @test */
    public function 送信時に、お問い合わせ内容が未入力だとリダイレクトされ、バリデーションエラーになる(): void
    {
        $contact = Contact::factory()->make();

        //直前のページを指定
        $this->from(route('contact.confirm'));

        $response = $this->post(route('contact.store'), [
            'category_id' => $contact->category->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'tel' => $contact->tel,
            'address' => $contact->address,
            'detail' => '',
        ]);

        $response->assertRedirect(route('contact.confirm'));
        $response->assertSessionHasErrors(['detail']);
    }

    /** @test */
    public function ログイン済みの管理者はフィルタ条件を指定してCSVをダウンロードできる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $matchCategory = Category::factory()->create();
        $unmatchCategory = Category::factory()->create();

        $matchContact = Contact::factory()->create([
            'first_name' => '山田',
            'gender' => 1,
            'category_id' => $matchCategory->id,
            'created_at' => '2026-05-24 10:00:00',
        ]);
        $unmatchContact = Contact::factory()->create([
            'first_name' => '鈴木',
            'gender' => 2,
            'category_id' => $unmatchCategory->id,
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('contact.export', [
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $matchCategory->id,
            'date' => '2026-05-24',
        ]));

        // Assert
        $response->assertStatus(200);

        $csvContent = $response->streamedContent(); //csvの中身の取得
        // 一致するデータが含まれ、一致しないデータが含まれていないことを検証
        $this->assertStringContainsString($matchContact->email, $csvContent);
        $this->assertStringNotContainsString($unmatchContact->email, $csvContent);
    }

    /** @test */
    public function ログイン済みの管理者はフィルタ無指定時に全件を新着順でダウンロードできる(): void
    {
        // Arrange
        $user = User::factory()->create();

        $oldContact = Contact::factory()->create(['created_at' => now()->subDay()]);
        $newContact = Contact::factory()->create(['created_at' => now()]);

        // Act
        $response = $this->actingAs($user)->get(route('contact.export'));

        // Assert
        $response->assertStatus(200);

        $csvContent = $response->streamedContent();

        // 両方のデータがあるか確認
        $this->assertStringContainsString($newContact->email, $csvContent);
        $this->assertStringContainsString($oldContact->email, $csvContent);

        // 新着順の検証
        $this->assertTrue(
            strpos($csvContent, $newContact->email) < strpos($csvContent, $oldContact->email),
            'CSVの内容が新着順（新しいものが上）になっていません。'
        );
    }
}
