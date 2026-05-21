<?php

namespace App\Http\Controllers;

use App\Http\Requests\TagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    /**
     * タグを追加する(新規作成)
     */
    public function store(TagRequest $request)
    {
        Tag::create($request->validated());

        return redirect()->route('admin.index')
            ->with('success', 'タグを作成しました');
    }

    /**
     * タグを編集する
     */
    public function edit(tag $tag)
    {
        return view('admin/tags/edit', compact('tag'));
    }

    /**
     * タグを更新する
     */
    public function update(TagRequest $request, tag $tag)
    {
        $tag->update($request->validated());

        return redirect()->route('admin.index')
            ->with('success', 'タグを更新しました');
    }

    /**
     * タグを削除する
     */
    public function destroy(tag $tag)
    {
        // 中間テーブル（contact_tag_table）の関連データを削除
        $tag->contacts()->detach();

        $tag->delete();

        return redirect()->route('admin.index')
            ->with('success', 'タグを削除しました');
    }
}
