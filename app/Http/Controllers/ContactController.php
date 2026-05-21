<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Models\Contact;
use App\Http\Requests\StoreContactRequest;

class ContactController extends Controller
{
    /**
     * ページを表示する
     */
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index',compact('categories','tags'));
    }

    /**
     * 入力必須項目を入力して「確認画面」ボタンを押す
     */
    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $category = Category::find($validated['category_id']);

        // タグを選択していない場合は空のコレクション、選択している場合はそのタグだけをwhereInで取得する
        $tags = collect();
        if (!empty($validated['tag_ids'])) {
            $tags = Tag::whereIn('id', $validated['tag_ids'])->get();
        }

        return view('contact.confirm',compact('validated','category','tags'));
    }

    /**
     * 「送信」ボタンを押す
     */
    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $contact = Contact::create($validated);

        // タグ有＆配列で渡されている場合に限り中間テーブルに保存
        if ($request->has('tag_ids') && is_array($request->tag_ids)) {
            $contact->tags()->attach($request->tag_ids);
        }

        return redirect()->route('contact.thanks');
    }

    /**
     * サンクスページが表示される
     */
    public function thanks()
    {
        return view('contact.thanks');
    }
}
