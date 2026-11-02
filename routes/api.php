<?php

use App\Http\Controllers\Api\AboutController;
use App\Http\Controllers\Api\HomeController;
use Illuminate\Support\Facades\Route;

// Konten halaman Tentang Kami / About Us (publik, read-only).
Route::get('/about', [AboutController::class, 'index'])->name('api.about');
Route::get('/about/{locale}', [AboutController::class, 'show'])->name('api.about.locale');

// Konten halaman Beranda / Home (publik, read-only).
Route::get('/home', [HomeController::class, 'index'])->name('api.home');
Route::get('/home/{locale}', [HomeController::class, 'show'])->name('api.home.locale');
