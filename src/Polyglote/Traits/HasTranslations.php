<?php

declare(strict_types=1);

namespace MetaFramework\Polyglote\Traits;

use MetaFramework\Accessors\Locale;
use MetaFramework\Polyglote\Exceptions\AttributeIsNotTranslatable;
use Spatie\Translatable\HasTranslations as SpatieHasTranslations;

trait HasTranslations
{
    use SpatieHasTranslations {
        initializeHasTranslations as private initializeSpatieHasTranslations;
        getAttributeValue as private getSpatieAttributeValue;
        setAttribute as private setSpatieAttribute;
    }

    public function initializeHasTranslations(): void
    {
        if (Locale::multilang()) {
            $this->initializeSpatieHasTranslations();
        }
    }

    public function getAttributeValue($key): mixed
    {
        if (!Locale::multilang()) {
            return parent::getAttributeValue($key);
        }

        return $this->getSpatieAttributeValue($key);
    }

    public function setAttribute($key, $value)
    {
        if (!Locale::multilang()) {
            return parent::setAttribute($key, $value);
        }

        if ($this->isTranslatableAttribute($key) && is_array($value)) {
            return $this->setTranslations($key, $value);
        }

        return $this->setSpatieAttribute($key, $value);
    }

    protected function guardAgainstNonTranslatableAttribute(string $key): void
    {
        if (!$this->isTranslatableAttribute($key)) {
            throw AttributeIsNotTranslatable::make($key, $this);
        }
    }

    public function getCasts(): array
    {
        if (!Locale::multilang()) {
            return parent::getCasts();
        }

        return array_merge(
            parent::getCasts(),
            array_fill_keys($this->getTranslatableAttributes(), 'array'),
        );
    }
}
