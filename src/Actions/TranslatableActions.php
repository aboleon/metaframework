<?php

declare(strict_types=1);

namespace MetaFramework\Actions;

use MetaFramework\Services\TranslatableTranslator;
use MetaFramework\Support\Traits\Ajax;

class TranslatableActions
{
    use Ajax;

    public function translateTranslatables(): self
    {
        $sourceLocale = (string) request('source_locale');
        $targetLocales = (array) request('target_locales', []);
        $payloadItems = (array) request('payload', []);
        $availableLocales = config('mfw.translatable.locales', []);

        if ($sourceLocale === '' || !in_array($sourceLocale, $availableLocales, true)) {
            $this->responseError(__('mfw-support::mfw-support.ajax.request_cannot_be_interpreted'));

            return $this;
        }

        $targetLocales = array_values(array_filter($targetLocales, function ($locale) use ($availableLocales, $sourceLocale) {
            return in_array($locale, $availableLocales, true) && $locale !== $sourceLocale;
        }));

        if (empty($targetLocales)) {
            $this->responseError(__('mfw-support::mfw-support.ajax.request_cannot_be_interpreted'));

            return $this;
        }

        $payload = [];
        foreach ($payloadItems as $item) {
            $key = trim((string) ($item['key'] ?? ''));
            $value = trim((string) ($item['value'] ?? ''));
            if ($key === '' || $value === '') {
                continue;
            }
            $payload[$key] = $value;
        }

        if (empty($payload)) {
            $this->responseError(__('mfw-support::mfw-support.ajax.request_cannot_be_interpreted'));

            return $this;
        }

        if (!config('mfw-api.deepl')) {
            $this->responseError(__('mfw-support::mfw-support.errors.error'));

            return $this;
        }

        $translator = new TranslatableTranslator;
        $translations = $translator->translations($payload, $sourceLocale, $targetLocales);

        $this->responseElement('translations', $translations);
        $this->responseSuccess(__('mfw.translation_complete'));

        return $this;
    }
}
