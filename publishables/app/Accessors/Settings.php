<?php

namespace App\Accessors;

use App\Models\Setting;

class Settings
{

    public static function getValue(string $name): string|null
    {
        return Setting::where('name', $name)->first()?->value;
    }
}