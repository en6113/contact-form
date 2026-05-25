<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
    /**
     * お問い合わせ一覧を取得/
     */
    public function index(IndexContactRequest $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->input('per_page', 20), 100);

        $contacts = Contact::with(['category', 'tags'])
            ->keywordSearch($request->keyword)
            ->genderSearch($request->gender)
            ->categorySearch($request->category_id)
            ->dateSearch($request->date)
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        return ContactResource::collection($contacts);
    }

    /**
     * お問い合わせ詳細を取得
     */
    public function show(Contact $contact): ContactResource
    {
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    /**
     * お問い合わせを作成(登録)
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $contact = DB::transaction(function () use ($validated) {
            $contact = Contact::create($validated);

            // 中間テーブルへの保存
            if (! empty($validated['tag_ids']) && is_array($validated['tag_ids'])) {
                $contact->tags()->attach($validated['tag_ids']);
            }

            return $contact;
        });

        return (new ContactResource($contact->load(['category', 'tags'])))
            ->additional(['message' => 'お問い合わせを登録しました'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * お問い合わせを更新
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $validated = $request->validated();
        $contact->update($validated);

        if (! empty($validated['tag_ids']) && is_array($validated['tag_ids'])) {
            $contact->tags()->sync($validated['tag_ids']);
        } else {
            $contact->tags()->sync([]); // タグが未選択で送られてきた場合に既存のタグを外す
        }

        return (new ContactResource($contact))
            ->additional(['message' => 'お問い合わせを更新しました']);
    }

    /**
     * お問い合わせを削除
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}
