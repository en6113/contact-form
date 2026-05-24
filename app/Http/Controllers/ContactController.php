<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use App\Models\Contact;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\ExportContactRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    /**
     * 検索結果をBOM付きCSVとしてエクスポートする
     * @param ExportContactRequest $request
     * @return StreamedResponse
     */
    public function export(ExportContactRequest $request)
    {
        $contacts = Contact::with('category')
            ->keywordSearch($request->keyword)
            ->genderSearch($request->gender)
            ->categorySearch($request->category_id)
            ->dateSearch($request->date)
            ->latest()
            ->cursor(); //データを少しずつ読み込んでCSVに書き出す

        // ヘッダー情報を設定（ダウンロードを促す設定）
        $fileName = 'contacts_' . now()->format('YmdHis') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        // StreamedResponse を使って、CSVを生成しながらブラウザに順次出力する
        return response()->stream(function () use ($contacts) {
            $stream = fopen('php://output', 'w'); // 標準出力（ブラウザへのレスポンス）を開く
            fwrite($stream, pack('C*', 0xEF, 0xBB, 0xBF)); // 文字化けを防ぐため、ファイルの先頭にBOM（UTF-8）を書き込む

            // CSVの1行目（ヘッダー）を出力
            $headerRow = [
                'ID', '氏名', '性別', 'メール', '電話', '住所', 
                '建物', 'カテゴリ', '内容', '作成日時'
            ];
            fputcsv($stream, $headerRow);

            // データを1件ずつループしてCSVの行に変換
            foreach ($contacts as $contact) {
                // 性別の数値を文字列に変換
                $genderText = $contact->gender === 1 ? '男性' : ($contact->gender === 2 ? '女性' : 'その他');
                // first_nameとlast_nameを氏名に統合
                $fullName = $contact->last_name . ' ' . $contact->first_name;

                $row = [
                    $contact->id,
                    $fullName,
                    $genderText,
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category?->content ?? '未設定', // リレーションからカテゴリ名を取得（Null安全）
                    $contact->detail,
                    $contact->created_at->format('Y-m-d H:i:s'),
                ];

                fputcsv($stream, $row);
            }

            // 最後にファイルを閉じる
            fclose($stream);
        }, 200, $headers);
    }
}
