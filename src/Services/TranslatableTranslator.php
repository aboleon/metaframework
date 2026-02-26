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

    /**
     * @var array<string, string|null>
     */
    private array $translationCache = [];

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
        if (!$translator || empty($normalized)) {
            return [];
        }

        $sourceLang = $this->mapLocale($sourceLocale, true);
        if (!$sourceLang) {
            return [];
        }

        $translations = [];
        $targets = array_values(array_unique($targetLocales));

        foreach ($targets as $locale) {
            if ($locale === $sourceLocale) {
                continue;
            }

            $targetLang = $this->mapLocale($locale, false);
            if (!$targetLang) {
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
        if (!$this->apiKey) {
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
        if ($this->isMarkdownLikeMultiline($text)) {
            return $this->translateMarkdownPreservingLayout($translator, $text, $sourceLang, $targetLang);
        }

        return $this->requestTranslation($translator, $text, $sourceLang, $targetLang, $this->translateOptions($text));
    }

    private function requestTranslation(
        Translator $translator,
        string $text,
        ?string $sourceLang,
        string $targetLang,
        array $options,
    ): ?string {
        $cacheKey = $this->translationCacheKey($text, $sourceLang, $targetLang, $options);

        if (array_key_exists($cacheKey, $this->translationCache)) {
            return $this->translationCache[$cacheKey];
        }

        try {
            $result = $translator->translateText($text, $sourceLang, $targetLang, $options);
        } catch (DeepLException $exception) {
            $this->responses[] = [
                'text' => $text,
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
                'translated' => null,
                'error' => $exception->getMessage(),
            ];

            $this->translationCache[$cacheKey] = null;

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

            $this->translationCache[$cacheKey] = $translated;

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

        $this->translationCache[$cacheKey] = $translated;

        return $translated;
    }

    /**
     * @param  array<int, string>  $texts
     * @param  array<string, bool|string>  $options
     */
    private function preloadTranslationsBatch(
        Translator $translator,
        array $texts,
        ?string $sourceLang,
        string $targetLang,
        array $options,
    ): void {
        $pending = [];

        foreach ($texts as $text) {
            if ($text === '') {
                continue;
            }

            $cacheKey = $this->translationCacheKey($text, $sourceLang, $targetLang, $options);
            if (array_key_exists($cacheKey, $this->translationCache) || array_key_exists($cacheKey, $pending)) {
                continue;
            }

            $pending[$cacheKey] = $text;
        }

        if (empty($pending)) {
            return;
        }

        $chunk = [];
        $chunkChars = 0;

        foreach ($pending as $cacheKey => $text) {
            $textLength = mb_strlen($text);
            $wouldExceedItemCount = count($chunk) >= 40;
            $wouldExceedCharCount = $chunkChars > 0 && ($chunkChars + $textLength) > 12000;

            if ($wouldExceedItemCount || $wouldExceedCharCount) {
                $this->executePreloadChunk($translator, $chunk, $sourceLang, $targetLang, $options);
                $chunk = [];
                $chunkChars = 0;
            }

            $chunk[$cacheKey] = $text;
            $chunkChars += $textLength;
        }

        if (!empty($chunk)) {
            $this->executePreloadChunk($translator, $chunk, $sourceLang, $targetLang, $options);
        }
    }

    /**
     * @param  array<string, string>  $chunk
     * @param  array<string, bool|string>  $options
     */
    private function executePreloadChunk(
        Translator $translator,
        array $chunk,
        ?string $sourceLang,
        string $targetLang,
        array $options,
    ): void {
        $chunkTexts = array_values($chunk);
        $chunkKeys = array_keys($chunk);

        try {
            $result = $translator->translateText($chunkTexts, $sourceLang, $targetLang, $options);
        } catch (DeepLException $exception) {
            foreach ($chunkTexts as $index => $chunkText) {
                $this->responses[] = [
                    'text' => $chunkText,
                    'source_lang' => $sourceLang,
                    'target_lang' => $targetLang,
                    'translated' => null,
                    'error' => $exception->getMessage(),
                ];
                $this->translationCache[$chunkKeys[$index]] = null;
            }

            return;
        }

        $results = is_array($result) ? $result : [$result];

        foreach ($chunkTexts as $index => $chunkText) {
            $translated = $results[$index]->text ?? null;
            $this->responses[] = [
                'text' => $chunkText,
                'source_lang' => $sourceLang,
                'target_lang' => $targetLang,
                'translated' => $translated,
                'error' => null,
            ];
            $this->translationCache[$chunkKeys[$index]] = $translated;
        }
    }

    /**
     * @param  array<string, bool|string>  $options
     */
    private function translationCacheKey(string $text, ?string $sourceLang, string $targetLang, array $options): string
    {
        return md5(json_encode([
            'text' => $text,
            'source_lang' => $sourceLang,
            'target_lang' => $targetLang,
            'options' => $options,
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * @return array<string, bool|string>
     */
    private function translateOptions(string $text): array
    {
        return [
            TranslateTextOptions::PRESERVE_FORMATTING => true,
            TranslateTextOptions::SPLIT_SENTENCES => $this->isMarkdownLikeMultiline($text) ? '1' : 'nonewlines',
        ];
    }

    private function isMarkdownLikeMultiline(string $text): bool
    {
        if (!str_contains($text, "\n") && !str_contains($text, "\r")) {
            return false;
        }

        return (bool) preg_match('/(^|\R)\s*(#{1,6}\s|[-*+]\s|\d+\.\s|>\s|```|\|.+\|)/u', $text);
    }

    private function translateMarkdownPreservingLayout(
        Translator $translator,
        string $text,
        ?string $sourceLang,
        string $targetLang,
    ): ?string {
        $this->preloadMarkdownTranslations($translator, $text, $sourceLang, $targetLang);

        $parts = preg_split('/(\R)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false) {
            return $this->requestTranslation($translator, $text, $sourceLang, $targetLang, $this->translateOptions($text));
        }

        $translated = '';
        $inFence = false;

        for ($index = 0; $index < count($parts); $index += 2) {
            $line = $parts[$index] ?? '';
            $newline = $parts[$index + 1] ?? '';

            if ($this->isFenceLine($line)) {
                $inFence = !$inFence;
                $translated .= $line . $newline;

                continue;
            }

            if ($inFence || trim($line) === '') {
                $translated .= $line . $newline;

                continue;
            }

            $translatedLine = $this->translateMarkdownLine($translator, $line, $sourceLang, $targetLang);
            $translated .= ($translatedLine ?? $line) . $newline;
        }

        return $translated;
    }

    private function preloadMarkdownTranslations(
        Translator $translator,
        string $text,
        ?string $sourceLang,
        string $targetLang,
    ): void {
        $parts = preg_split('/(\R)/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($parts === false) {
            return;
        }

        $segments = [];
        $inFence = false;

        for ($index = 0; $index < count($parts); $index += 2) {
            $line = $parts[$index] ?? '';

            if ($this->isFenceLine($line)) {
                $inFence = !$inFence;

                continue;
            }

            if ($inFence || trim($line) === '') {
                continue;
            }

            foreach ($this->collectMarkdownLineSegments($line) as $segment) {
                $segments[] = $segment;
            }
        }

        $this->preloadTranslationsBatch($translator, $segments, $sourceLang, $targetLang, [
            TranslateTextOptions::PRESERVE_FORMATTING => true,
            TranslateTextOptions::SPLIT_SENTENCES => 'nonewlines',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function collectMarkdownLineSegments(string $line): array
    {
        if ($this->isMarkdownTableSeparatorLine($line)) {
            return [];
        }

        if ($this->isMarkdownTableRowLine($line)) {
            return $this->collectMarkdownTableRowSegments($line);
        }

        if (preg_match('/^(\s*(?:>\s*)*#{1,6}\s+)(.+)$/u', $line, $matches) === 1) {
            return $this->collectTranslatableInlineSegments($matches[2]);
        }

        if (preg_match('/^(\s*(?:>\s*)*(?:[-*+]\s+(?:\[[ xX]\]\s+)?)?)(.+)$/u', $line, $matches) === 1
            && trim($matches[1]) !== '') {
            return $this->collectTranslatableInlineSegments($matches[2]);
        }

        if (preg_match('/^(\s*(?:>\s*)*\d+[.)]\s+)(.+)$/u', $line, $matches) === 1) {
            return $this->collectTranslatableInlineSegments($matches[2]);
        }

        if (preg_match('/^(\s*(?:>\s*)+)(.+)$/u', $line, $matches) === 1) {
            return $this->collectTranslatableInlineSegments($matches[2]);
        }

        return $this->collectTranslatableInlineSegments($line);
    }

    /**
     * @return array<int, string>
     */
    private function collectMarkdownTableRowSegments(string $line): array
    {
        $segments = [];
        $cells = explode('|', $line);

        foreach ($cells as $cell) {
            if (preg_match('/^(\s*)(.*?)(\s*)$/us', $cell, $matches) !== 1) {
                continue;
            }

            $content = $matches[2];
            if ($content === '' || $this->isMarkdownTableSeparatorLine($content)) {
                continue;
            }

            foreach ($this->collectTranslatableInlineSegments($content) as $segment) {
                $segments[] = $segment;
            }
        }

        return $segments;
    }

    /**
     * @return array<int, string>
     */
    private function collectTranslatableInlineSegments(string $text): array
    {
        $trimmed = trim($text);

        if ($trimmed === '' || preg_match('/^`{1,3}[^`]*`{1,3}$/u', $trimmed) === 1) {
            return [];
        }

        if (preg_match('/^(\s*)(.*?)(\s*)$/us', $text, $matches) !== 1) {
            return [];
        }

        $content = $matches[2];

        return $content === '' ? [] : [$content];
    }

    private function translateMarkdownLine(
        Translator $translator,
        string $line,
        ?string $sourceLang,
        string $targetLang,
    ): ?string {
        if ($this->isMarkdownTableSeparatorLine($line)) {
            return $line;
        }

        if ($this->isMarkdownTableRowLine($line)) {
            return $this->translateMarkdownTableRow($translator, $line, $sourceLang, $targetLang);
        }

        if (preg_match('/^(\s*(?:>\s*)*#{1,6}\s+)(.+)$/u', $line, $matches) === 1) {
            $translated = $this->translateMarkdownInlineText($translator, $matches[2], $sourceLang, $targetLang);

            return $matches[1] . ($translated ?? $matches[2]);
        }

        if (preg_match('/^(\s*(?:>\s*)*(?:[-*+]\s+(?:\[[ xX]\]\s+)?)?)(.+)$/u', $line, $matches) === 1
            && trim($matches[1]) !== '') {
            $translated = $this->translateMarkdownInlineText($translator, $matches[2], $sourceLang, $targetLang);

            return $matches[1] . ($translated ?? $matches[2]);
        }

        if (preg_match('/^(\s*(?:>\s*)*\d+[.)]\s+)(.+)$/u', $line, $matches) === 1) {
            $translated = $this->translateMarkdownInlineText($translator, $matches[2], $sourceLang, $targetLang);

            return $matches[1] . ($translated ?? $matches[2]);
        }

        if (preg_match('/^(\s*(?:>\s*)+)(.+)$/u', $line, $matches) === 1) {
            $translated = $this->translateMarkdownInlineText($translator, $matches[2], $sourceLang, $targetLang);

            return $matches[1] . ($translated ?? $matches[2]);
        }

        return $this->translateMarkdownInlineText($translator, $line, $sourceLang, $targetLang);
    }

    private function translateMarkdownTableRow(
        Translator $translator,
        string $line,
        ?string $sourceLang,
        string $targetLang,
    ): string {
        $cells = explode('|', $line);

        foreach ($cells as $index => $cell) {
            $cells[$index] = $this->translateMarkdownTableCell($translator, $cell, $sourceLang, $targetLang);
        }

        return implode('|', $cells);
    }

    private function translateMarkdownTableCell(
        Translator $translator,
        string $cell,
        ?string $sourceLang,
        string $targetLang,
    ): string {
        if (preg_match('/^(\s*)(.*?)(\s*)$/us', $cell, $matches) !== 1) {
            return $cell;
        }

        $leading = $matches[1];
        $content = $matches[2];
        $trailing = $matches[3];

        if ($content === '' || $this->isMarkdownTableSeparatorLine($content)) {
            return $cell;
        }

        $translated = $this->translateMarkdownInlineText($translator, $content, $sourceLang, $targetLang);

        return $leading . ($translated ?? $content) . $trailing;
    }

    private function translateMarkdownInlineText(
        Translator $translator,
        string $text,
        ?string $sourceLang,
        string $targetLang,
    ): ?string {
        $trimmed = trim($text);

        if ($trimmed === '' || preg_match('/^`{1,3}[^`]*`{1,3}$/u', $trimmed) === 1) {
            return $text;
        }

        if (preg_match('/^(\s*)(.*?)(\s*)$/us', $text, $matches) !== 1) {
            return $this->requestTranslation($translator, $text, $sourceLang, $targetLang, $this->translateOptions($text));
        }

        $leading = $matches[1];
        $content = $matches[2];
        $trailing = $matches[3];

        if ($content === '') {
            return $text;
        }

        $translated = $this->requestTranslation($translator, $content, $sourceLang, $targetLang, [
            TranslateTextOptions::PRESERVE_FORMATTING => true,
            TranslateTextOptions::SPLIT_SENTENCES => 'nonewlines',
        ]);

        return $translated === null ? null : $leading . $translated . $trailing;
    }

    private function isFenceLine(string $line): bool
    {
        return preg_match('/^\s*(```|~~~)/u', $line) === 1;
    }

    private function isMarkdownTableRowLine(string $line): bool
    {
        $trimmed = trim($line);

        if ($trimmed === '' || !str_contains($trimmed, '|')) {
            return false;
        }

        return substr_count($trimmed, '|') >= 2 || str_starts_with($trimmed, '|');
    }

    private function isMarkdownTableSeparatorLine(string $line): bool
    {
        return preg_match('/^\s*\|?(?:\s*:?-{2,}:?\s*\|)+\s*:?-{2,}:?\s*\|?\s*$/u', $line) === 1;
    }

    private function containsLatinCharacters(string $text): bool
    {
        return (bool) preg_match('/[A-Za-z]/u', $text);
    }

    private function transliterateLatinToCyrillic(string $text): string
    {
        if (!extension_loaded('intl')) {
            return $text;
        }

        $transliterated = transliterator_transliterate('Latin-Cyrillic', $text);

        return $transliterated !== false ? $transliterated : $text;
    }
}
