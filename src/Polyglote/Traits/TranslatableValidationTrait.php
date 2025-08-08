<?php

namespace MetaFramework\Polyglote\Traits;

/**
 * Key Features of the Trait
 * Available Methods:
 *
 * 1. buildTranslatableRules(array $fields)
 *
 * Creates rules for all active locales with same validation
 * Use when all locales are equally required
 *
 *
 * 2. buildTranslatableRulesWithFallbackRequired(array $fields)
 *
 * Only fallback locale is required, others are nullable
 * Use when you want at least one language but others are optional
 *
 *
 * 3. buildTranslatableRulesWithPrefix(string $prefix, array $fields)
 *
 * Same as #1 but with a prefix (e.g., 'service_texts.title.fr')
 *
 *
 * 4. buildTranslatableRulesWithPrefixFallbackRequired(string $prefix, array $fields)
 *
 * Same as #2 but with a prefix (e.g., 'place.description.fr' required, 'place.description.en' optional)
 *
 *
 * 5. buildTranslatableMessages(array $messages)
 *
 * Creates localized validation messages
 * Automatically adds locale labels when multiple locales exist
 *
 *
 *6.  buildTranslatableMessagesWithPrefix(string $prefix, array $messages)
 *
 * Same as #5 but with prefix support
 *
 *
 *
 * Configuration Integration:
 * The trait automatically reads from your config/mfw.php:
 *
 * translatable.active_locales - Which locales to validate
 * translatable.fallback_locale - Which locale is the primary one
 */

trait TranslatableValidationTrait
{
    /**
     * Get all active locales from config
     */
    protected function getActiveLocales(): array
    {
        return config('mfw.translatable.active_locales', ['fr', 'en']);
    }

    /**
     * Get the fallback locale
     */
    protected function getFallbackLocale(): string
    {
        return config('mfw.translatable.fallback_locale', 'fr');
    }

    /**
     * Build validation rules for translatable fields
     *
     * @param  array  $translatableFields  Array of translatable field configurations
     *
     * @return array
     *
     * Example usage:
     * $this->buildTranslatableRules([
     *     'title' => 'required|string|max:255',
     *     'description' => 'required|string',
     *     'slug' => 'nullable|string'
     * ])
     */
    protected function buildTranslatableRules(array $translatableFields): array
    {
        $rules   = [];
        $locales = $this->getActiveLocales();

        foreach ($translatableFields as $field => $rule) {
            foreach ($locales as $locale) {
                $rules["{$field}.{$locale}"] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Build validation messages for translatable fields
     *
     * @param  array  $translatableMessages  Array of translatable field message configurations
     *
     * @return array
     *
     * Example usage:
     * $this->buildTranslatableMessages([
     *     'title.required' => __('validation.required', ['attribute' => __('mfw.title')]),
     *     'description.required' => __('validation.required', ['attribute' => __('mfw.description')])
     * ])
     */
    protected function buildTranslatableMessages(array $translatableMessages): array
    {
        $messages = [];
        $locales  = $this->getActiveLocales();

        foreach ($translatableMessages as $fieldRule => $message) {
            foreach ($locales as $locale) {
                [$field, $ruleType] = explode('.', $fieldRule, 2);
                $localeLabel = __('mwf-lang.'.$locale.'.label');

                // Add locale info to the message
                $localizedMessage = $message;
                if (count($locales) > 1) {
                    $localizedMessage .= " (en {$localeLabel})";
                }

                $messages["{$field}.{$locale}.{$ruleType}"] = $localizedMessage;
            }
        }

        return $messages;
    }

    /**
     * Build validation rules for translatable fields with prefix
     * Useful for nested validation or when using prefixes
     *
     * @param  string  $prefix              The prefix to prepend
     * @param  array   $translatableFields  Array of translatable field configurations
     *
     * @return array
     */
    protected function buildTranslatableRulesWithPrefix(string $prefix, array $translatableFields): array
    {
        $rules   = [];
        $locales = $this->getActiveLocales();

        foreach ($translatableFields as $field => $rule) {
            foreach ($locales as $locale) {
                $rules["{$prefix}.{$field}.{$locale}"] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Build validation messages for translatable fields with prefix
     *
     * @param  string  $prefix                The prefix to prepend
     * @param  array   $translatableMessages  Array of translatable field message configurations
     *
     * @return array
     */
    protected function buildTranslatableMessagesWithPrefix(string $prefix, array $translatableMessages): array
    {
        $messages = [];
        $locales  = $this->getActiveLocales();

        foreach ($translatableMessages as $fieldRule => $message) {
            foreach ($locales as $locale) {
                [$field, $ruleType] = explode('.', $fieldRule, 2);
                $localeLabel = __('mfw-lang.'.$locale.'.label');

                // Add locale info to the message
                $localizedMessage = $message;
                if (count($locales) > 1) {
                    $localizedMessage .= " (en {$localeLabel})";
                }

                $messages["{$prefix}.{$field}.{$locale}.{$ruleType}"] = $localizedMessage;
            }
        }

        return $messages;
    }

    /**
     * Build validation rules where only the fallback locale is required
     * and other locales are optional
     *
     * @param  array  $translatableFields  Array of translatable field configurations
     *
     * @return array
     */
    protected function buildTranslatableRulesWithFallbackRequired(array $translatableFields): array
    {
        $rules          = [];
        $locales        = $this->getActiveLocales();
        $fallbackLocale = $this->getFallbackLocale();

        foreach ($translatableFields as $field => $rule) {
            foreach ($locales as $locale) {
                if ($locale === $fallbackLocale) {
                    // Keep original rule for fallback locale
                    $rules["{$field}.{$locale}"] = $rule;
                } else {
                    // Make optional for other locales
                    $optionalRule                = str_replace('required', 'nullable', $rule);
                    $rules["{$field}.{$locale}"] = $optionalRule;
                }
            }
        }

        return $rules;
    }

    /**
     * Build validation rules with prefix where only the fallback locale is required
     * and other locales are optional
     *
     * @param  string  $prefix              The prefix to prepend
     * @param  array   $translatableFields  Array of translatable field configurations
     *
     * @return array
     */
    protected function buildTranslatableRulesWithPrefixFallbackRequired(string $prefix, array $translatableFields): array
    {
        $rules          = [];
        $locales        = $this->getActiveLocales();
        $fallbackLocale = $this->getFallbackLocale();

        foreach ($translatableFields as $field => $rule) {
            foreach ($locales as $locale) {
                if ($locale === $fallbackLocale) {
                    // Keep original rule for fallback locale
                    $rules["{$prefix}{$field}.{$locale}"] = $rule;
                } else {
                    // Make optional for other locales
                    $optionalRule                         = str_replace('required', 'nullable', $rule);
                    $rules["{$prefix}{$field}.{$locale}"] = $optionalRule;
                }
            }
        }

        return $rules;
    }
}
