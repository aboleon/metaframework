<?php

declare(strict_types=1);

namespace MetaFramework\Polyglote\Traits;

use Illuminate\Support\Facades\Cache;

trait TransliterationTrait
{
    use CyrillicContentTrait;

    /**
     * Get transliterated versions of search term for Cyrillic locales.
     */
    private function getSearchVariants(string $searchTerm, ?string $locale = null, bool $force = false): array
    {
        $locales = config('mfw.translatable.locales', []);
        $isCyrillicLocale = $this->isCyrillicLocale($locale) && in_array($locale, $locales, true);

        if ((! $isCyrillicLocale && ! $force) || ! extension_loaded('intl')) {
            return [$searchTerm];
        }

        $cacheKey = sprintf('trans_search_%s_%s_%s', $locale ?? 'na', $force ? 'force' : 'auto', $searchTerm);

        return Cache::remember($cacheKey, 3600, static function () use ($searchTerm) {
            $variants = [$searchTerm];

            if (preg_match('/[А-Яа-яЁё]/u', $searchTerm)) {
                $latin = transliterator_transliterate('Cyrillic-Latin', $searchTerm);
                if ($latin !== $searchTerm && $latin !== false) {
                    $variants[] = $latin;
                    if (preg_match('/[иИ]/u', $searchTerm)) {
                        $variants[] = str_replace(['i', 'I'], ['y', 'Y'], $latin);
                    }
                }
            } else {
                $cyrillic = transliterator_transliterate('Latin-Cyrillic', $searchTerm);
                if ($cyrillic !== $searchTerm && $cyrillic !== false) {
                    $variants[] = $cyrillic;
                }
            }

            return array_values(array_unique($variants));
        });
    }
}
