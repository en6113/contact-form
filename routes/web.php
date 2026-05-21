<?php

use App\Http\Controllers\TagController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

//ユーザー用ルート
Route::get('/contacts', [ContactController::class,'index'])->name('contact.index');
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contact.confirm');
Route::post('/contacts', [ContactController::class,'store'])->name('contact.store');
Route::get('/thanks', [ContactController::class,'thanks'])->name('contact.thanks');

// 管理者用ルート
Route::middleware('auth')->group(function () {
    // 管理画面
    Route::get('/admin', fn() => '管理画面一覧（準備中）')->name('admin.index');
    Route::get('/admin/contacts/{contact}', fn() => 'お問い合わせ詳細ページ（準備中）')->name('admin.show');
    Route::delete('/admin/contacts/{contact}', fn() => 'お問い合わせ削除（準備中）');
    /* AdminController実装したら書き直す
    Route::get('/admin', [AdminController::class,'index'])->name('admin.index');
    Route::get('/admin/contacts/{contact}', [AdminController::class,'show'])->name('admin.show');
    Route::delete('/admin/contacts/{contact}', [AdminController::class,'destroy']);
    */

    //タグ関係
    Route::post('/admin/tags', [TagController::class,'store']);
    Route::get('/admin/tags/{tag}/edit', [TagController::class,'edit']);
    Route::post('/admin/tags/{tag}/edit', [TagController::class,'update']);
    Route::delete('/admin/tags/{tag}', [TagController::class, 'destroy']);
});