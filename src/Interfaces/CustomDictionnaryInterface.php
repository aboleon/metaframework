<?php

namespace MetaFramework\Interfaces;

interface CustomDictionnaryInterface
{
    public function translatables(): array;
    public function customData(): array;
}
