<?php

declare(strict_types=1);

namespace MetaFramework\Services\Validation;

trait ValidationPrefix
{
    private string $prefix;

    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix . '.';

        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }
}
