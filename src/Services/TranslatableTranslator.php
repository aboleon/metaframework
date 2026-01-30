<?php

declare(strict_types=1);

namespace MetaFramework\Services;

use DeepL\DeepLException;
use DeepL\TranslateTextOptions;
use DeepL\Translator;
use MetaFramework\Polyglote\Traits\CyrillicContentTrait;

class TranslatableTranslator
{
    use CyrillicContentTrait;

    private ?Translator $translator = null;
    private array $responses = [];

    public function __construct(
        private ?string $apiKey = null,
    ) {
        $this->apiKey = $this->apiKey ?? config('mfw-api.deepl');
    }

    /**
     * @param  array<string, string>  $payload
     * @param  array<int, string>  $targetLocales
     * @return array<string, array<string, string>>
     */
    public function translations(array $payload, string $sourceLocale, array $targetLocales): array
    {
        $normalized = [];
        foreach ($payload as $key => $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }

            $normalized[$key] = $value;
        }

        $translator = $this->translator();
        if (! $translator || empty($normalized)) {
            return [];
        }

        $sourceLang = $this->mapLocale($sourceLocale, true);
        if (! $sourceLang) {
            return [];
        }

        $translations = [];
        $targets = array_values(array_unique($targetLocales));

        foreach ($targets as $locale) {
            if ($locale === $sourceLocale) {
                continue;
            }

            $targetLang = $this->mapLocale($locale, false);
            if (! $targetLang) {
                continue;
            }

            foreach ($normalized as $field => $value) {
                $translated = $this->translateText($translator, $value, $sourceLang, $targetLang);
                if ($translated === null || $translated === '') {
                    continue;
                }

                if ($this->isCyrillicLocale($locale) && $this->containsLatinCharacters($translated)) {
                    $translated = $this->transliterateLatinToCyrillic($translated);
                }

                $translations[$field][$locale] = $translated;
            }
        }

        return $translations;
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    public function responses(): array
    {
        return $this->responses;
    }

    private function translator(): ?Translator
    {
        if (! $this->apiKey) {
            return null;
        }

        if ($this->translator) {
            return $this->translator;
        }

        $this->translator = new Translator($this->apiKey);

        return $this->translator;
    }

    private function mapLocale(string $locale, bool $isSource): ?string
    {
        $locale = strtolower($locale);

        return match ($locale) {
            'bg' => 'BG',
            'fr' => 'FR',
            'en' => $isSource ? 'EN' : 'EN-GB',
            'en-gb' => 'EN-GB',
            'en-us' => 'EN-US',
            default => null,
        };
    }

    private function translateText(Translator $translator, string $text, ?string $sourceLang, string $targetLang): ?string
    {
        try {
            $result = $translator->translateText($text, $sourceLang, $targetLang, [
                TranslateTextOptions::PRESERVE_FORMATTING => true,
                TranslateTextOptions::SPLIT_SENTENCES => 'nonewlines',
            ]);
        } catch (DeepLException $exception) {
            $this->responses[] = [
                'text' => $text,
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
                'translated' => null,
                'error' => $exception->getMessage(),
            ];

            return null;
        }

        if (is_array($result)) {
            $translated = $result[0]->text ?? null;
            $this->responses[] = [
                'text' => $text,
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
                'translated' => $translated,
                'error' => null,
            ];

            return $translated;
        }

        $translated = $result->text ?? null;
        $this->responses[] = [
            'text' => $text,
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
            'translated' => $translated,
            'error' => null,
        ];

        return $translated;
    }

    private function containsLatinCharacters(string $text): bool
    {
        return (bool) preg_match('/[A-Za-z]/u', $text);
    }

    private function transliterateLatinToCyrillic(string $text): string
    {
        if (! extension_loaded('intl')) {
            return $text;
        }

        $transliterated = transliterator_transliterate('Latin-Cyrillic', $text);

        return $transliterated !== false ? $transliterated : $text;
    }
}
