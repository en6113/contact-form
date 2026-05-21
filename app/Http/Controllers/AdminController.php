<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Contact;
use App\Models\Category;

class AdminController extends Controller
{
    /**
     * 管理画面の表示、条件検索
     */
    public function index(IndexContactRequest $request)
    {
        $query = Contact::with('category','tags');

        // キーワード検索（名前・メールの部分一致）
        //filled() を使うことで、空文字（""）で送信された場合に処理をスキップ
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;

            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', '%' . $keyword . '%')
                    ->orwhere('last_name', 'like', '%' . $keyword . '%')
                    ->orwhere('email', 'like', '%' . $keyword . '%');
            });
        }

        // 性別検索
        if ($request->filled('gender') && $request->gender !== '0') {
            $query->where('gender', $request->gender);
        }

        // カテゴリー検索
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 日付検索
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // ページネーション（appendsで検索クエリを引き継ぐ）
        $contacts = $query->paginate(7)->appends($request->query());;

        $categories = Category::all();

        return view('admin.index', compact('contacts','categories'));
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
