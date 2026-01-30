<?php

declare(strict_types=1);

namespace MetaFramework\Polyglote\Traits;

trait CyrillicContentTrait
{
    private const array CYRILLIC_LOCALES = ['bg', 'ru', 'uk', 'sr', 'mk', 'be', 'kk', 'ky', 'mn', 'tg', 'tt', 'uz'];

    protected function hasCyrillic(string $value): bool
    {
        return (bool) preg_match('/[А-яЁё]/u', $value);
    }

    protected function isCyrillicLocale(?string $locale): bool
    {
        if (!$locale) {
            return false;
        }

        $normalized = strtolower(str_replace('-', '_', $locale));
        $baseLocale = explode('_', $normalized, 2)[0];

        return in_array($baseLocale, self::CYRILLIC_LOCALES, true);
    }
}
