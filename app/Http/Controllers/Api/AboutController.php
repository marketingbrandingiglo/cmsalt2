<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AboutContent;
use Illuminate\Http\JsonResponse;

class AboutController extends Controller
{
    /**
     * GET /api/about — kedua locale sekaligus: {"id": {...}, "en": {...}}.
     */
    public function index(): JsonResponse
    {
        return response()->json(AboutContent::all());
    }

    /**
     * GET /api/about/{locale} — satu locale (id | en).
     */
    public function show(string $locale): JsonResponse
    {
        abort_unless(array_key_exists($locale, config('cms.locales')), 404, 'Locale tidak tersedia.');

        return response()->json(AboutContent::forLocale($locale));
    }
}
