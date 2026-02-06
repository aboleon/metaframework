<?php

declare(strict_types=1);

namespace MetaFramework\Polyglote\Traits;

use MetaFramework\Accessors\Locale;

trait Translation
{
    use HasTranslations;

    public array $translatable = [];

    /**
     * The Model Translatables
     */
    private array $translatables = [];

    private object $updatable;

    public static function bootTranslation(): void
    {
        static::retrieved(function ($model) {
            if (method_exists($model, 'defineTranslatables')) {
                $model->defineTranslatables();
            }
        });

        static::creating(function ($model) {
            if (method_exists($model, 'defineTranslatables')) {
                $model->defineTranslatables();
            }
        });
    }

    public function initializeTranslation(): void
    {
        if (method_exists($this, 'defineTranslatables')) {
            $this->defineTranslatables();
        }
    }

    public function translation(string $key, ?string $locale = null, bool $useFallbackLocale = true): mixed
    {
        if (Locale::multilang()) {
            return $this->translate($key, $locale, $useFallbackLocale);
        }

        if (str_contains($key, '.')) {
            $arrayable = explode('.', $key);
            $var       = array_shift($arrayable);

            switch (count($arrayable)) {
                case 1:
                    return $this->{$var}[$arrayable[0]];
                case 2:
                    return $this->{$var}[$arrayable[0]][$arrayable[1]];
                case 3:
                    return $this->{$var}[$arrayable[0]][$arrayable[1]][$arrayable[2]];
                case 4:
                    return $this->{$var}[$arrayable[0]][$arrayable[1]][$arrayable[2]][$arrayable[3]];
                case 5:
                    return $this->{$var}[$arrayable[0]][$arrayable[1]][$arrayable[2]][$arrayable[3]][$arrayable[4]];
            }
        }

        return $this->{$key} ?? '';
    }

    public function translatableInput(string $name, ?string $locale = null): string
    {
        if (Locale::multilang()) {
            return $name . '[' . $locale ?: Locale::locale() . ']';
        }

        return $name;
    }

    public function translatableFromRequest(string $key, ?string $locale = null): mixed
    {
        if (Locale::multilang()) {
            return request($key . '.' . $locale);
        }

        return request($key);
    }

    protected function defineTranslatables(): static
    {
        $this->translatable = array_keys($this->getTranslatableProperties());

        return $this;
    }

    public function saveTranslation($key, $locale, $value): void
    {
        Locale::multilang()
            ? $this->setTranslation($key, $locale, $value)
            : $this->{$key} = $value;
    }

    public function getTranslatableProperties(): array
    {
        if (method_exists($this, 'setTranslatables')) {
            $this->translatables = $this->setTranslatables();

            return $this->translatables;
        }

        return property_exists($this, 'fillables') ? $this->fillables : $this->translatables;
    }
}
