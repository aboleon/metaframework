<?php

namespace MetaFramework\Polyglote\Interfaces;

interface TranslatableInterface
{
    public function getTranslatableProperties(): array;

    public function setTranslatables(): array;
}