<?php

use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\EventMediaController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebsiteController::class, 'home'])->name('home');
Route::get('/event', [WebsiteController::class, 'events'])->name('events.index');
Route::get('/event/{slug}', [WebsiteController::class, 'event'])->name('events.show');
Route::get('/hasil', [WebsiteController::class, 'results'])->name('results.index');
Route::get('/hasil/demo', [WebsiteController::class, 'demo'])->name('results.demo');
Route::get('/berita', [WebsiteController::class, 'news'])->name('news.index');
Route::view('/konsultasi', 'contact')->name('contact');

Route::get('/berita/{slug}', [WebsiteController::class, 'article'])->name('news.show');
Route::get('/event-media/{event}/{filename}', EventMediaController::class)->where('filename', '[A-Za-z0-9]+\\.(jpg|jpeg|png|webp)')->name('event-media');

Route::middleware('guest')->group(function (): void {
    Route::get('/admin/login', [SessionController::class, 'create'])->name('login');
    Route::post('/admin/login', [SessionController::class, 'store'])->middleware('throttle:cms-login')->name('login.store');
});
Route::prefix('admin')->name('admin.')->middleware(['auth', 'auth.session', 'can:manage-cms'])->group(function (): void {
    Route::get('/', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/{id}/restore', [EventController::class, 'restore'])->whereNumber('id')->name('events.restore');
    Route::get('/events/{event}/preview', [EventController::class, 'preview'])->name('events.preview');
    Route::get('/events/{event}/article-preview', [EventController::class, 'articlePreview'])->name('events.article-preview');
    Route::get('/account', [SessionController::class, 'account'])->name('account');
    Route::put('/account/password', [SessionController::class, 'password'])->middleware('throttle:6,1')->name('password');
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout');
});
