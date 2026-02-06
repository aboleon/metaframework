<?php

declare(strict_types=1);

namespace MetaFramework\Accessors;

class Routing
{
    public static function backend(): string
    {
        return config('mfw.urls.backend', 'mfw');
    }
}
