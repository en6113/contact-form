<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;

class AdminController extends Controller
{
    /**
     * 管理画面の表示、条件検索
     */
    public function index(IndexContactRequest $request)
    {
        //モデルに検索ロジックあり（appends():検索クエリをページネーションに引き継ぐ）
        $contacts = Contact::with('category', 'tags')
            ->keywordSearch($request->keyword)
            ->genderSearch($request->gender)
            ->categorySearch($request->category_id)
            ->dateSearch($request->date)
            ->latest()
            ->paginate(7)
            ->appends($request->query());

        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact('contacts','categories','tags'));
    }

    /**
     * お問い合わせ詳細ページ表示
     */
    public function show(contact $contact)
    {
        $contact->load('category','tags');

        return view('admin.show',compact('contact'));
    }

    /**
     * お問い合わせの削除
     */
    public function destroy(contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.index')
            ->with('success', 'お問い合わせを削除しました');
    }
}
