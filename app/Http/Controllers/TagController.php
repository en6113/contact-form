<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;

class TagController extends Controller
{
    /**
     * タグを追加する(新規作成)
     */
    public function store(StoreTagRequest $request)
    {
        Tag::create($request->validated());

        return redirect()->route('admin.index')
            ->with('success', 'タグを作成しました');
    }

    /**
     * タグを編集する
     */
    public function edit(Tag $tag)
    {
        return view('admin/tags/edit', compact('tag'));
    }

    /**
     * タグを更新する
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $tag->update($request->validated());

        return redirect()->route('admin.index')
            ->with('success', 'タグを更新しました');
    }

    /**
     * タグを削除する
     */
    public function destroy(Tag $tag)
    {
        // 中間テーブル（contact_tag_table）の関連データを削除
        $tag->contacts()->detach();

        $tag->delete();

        return redirect()->route('admin.index')
            ->with('success', 'タグを削除しました');
    }
}
