<?php

use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebsiteController::class, 'home'])->name('home');
Route::get('/event', [WebsiteController::class, 'events'])->name('events.index');
Route::get('/event/{slug}', [WebsiteController::class, 'event'])->name('events.show');
Route::get('/hasil', [WebsiteController::class, 'results'])->name('results.index');
Route::get('/hasil/demo', [WebsiteController::class, 'demo'])->name('results.demo');
Route::get('/berita', [WebsiteController::class, 'news'])->name('news.index');
Route::view('/konsultasi', 'contact')->name('contact');
