<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

// ユーザー用ルート
Route::get('/', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contacts/confirm', [ContactController::class, 'confirm'])->name('contact.confirm');
Route::post('/contacts', [ContactController::class, 'store'])->name('contact.store');
Route::get('/thanks', [ContactController::class, 'thanks'])->name('contact.thanks');

// 管理者用ルート
Route::middleware('auth')->group(function () {
    // 管理画面
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/contacts/export', [ContactController::class, 'export'])->name('contact.export');
    Route::get('/admin/contacts/{contact}', [AdminController::class, 'show'])->name('admin.show');
    Route::delete('/admin/contacts/{contact}', [AdminController::class, 'destroy'])->name('admin.destroy');

    // タグ関係
    Route::post('/admin/tags', [TagController::class, 'store'])->name('tag.store');
    Route::get('/admin/tags/{tag}/edit', [TagController::class, 'edit'])->name('tag.edit');
    Route::put('/admin/tags/{tag}', [TagController::class, 'update'])->name('tag.update');
    Route::delete('/admin/tags/{tag}', [TagController::class, 'destroy'])->name('tag.destroy');
});
