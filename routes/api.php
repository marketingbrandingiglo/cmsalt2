<?php

use App\Http\Controllers\Api\AboutController;
use Illuminate\Support\Facades\Route;

// Konten halaman Tentang Kami / About Us (publik, read-only).
Route::get('/about', [AboutController::class, 'index'])->name('api.about');
Route::get('/about/{locale}', [AboutController::class, 'show'])->name('api.about.locale');
