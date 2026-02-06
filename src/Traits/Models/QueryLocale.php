<?php

declare(strict_types=1);

namespace MetaFramework\Traits\Models;

trait QueryLocale
{
    private ?string $query_locale = null;

    public function setQueryLocale(string $query_locale): self
    {
        $this->query_locale = $query_locale;

        return $this;
    }

    public function getQueryLocale(): string
    {
        if (!$this->query_locale) {
            $this->setQueryLocale(app()->getFallbackLocale());
        }

        return $this->query_locale;
    }
}
