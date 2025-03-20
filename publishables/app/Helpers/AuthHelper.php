<?php

namespace App\Helpers;

use App\Enum\UserType;

class AuthHelper
{

    public static function isDev(): bool
    {
        return auth()->user()->hasRole('dev');
    }

    public static function isAdmin()
    {
        return auth()?->user()?->type === UserType::SYSTEM->value;
    }

    public static function isFrontUser(): bool
    {
        return auth()?->user()?->type === UserType::ACCOUNT->value;
    }
}
