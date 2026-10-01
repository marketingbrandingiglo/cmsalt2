<?php

namespace App\Models\Concerns;

/**
 * Atribut JSON bilingual: {"id": ..., "en": ...}.
 * Bahasa Indonesia ("id") adalah locale utama sekaligus fallback.
 */
trait HasTranslations
{
    public function translate(string $attribute, string $locale): mixed
    {
        $value = $this->getAttribute($attribute);

        if (! is_array($value)) {
            return $value;
        }

        return filled($value[$locale] ?? null)
            ? $value[$locale]
            : ($value[config('cms.fallback_locale')] ?? null);
    }
}
