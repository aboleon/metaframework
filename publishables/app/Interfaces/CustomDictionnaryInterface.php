<?php

namespace App\Interfaces;

interface CustomDictionnaryInterface
{
    public function translatables(): array;
    public function customData(): array;
    public function mediaSettings(): array;
}
