<?php

declare(strict_types=1);

namespace App\Helpers;


use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Str;

class Sql
{

    public static function dump(Builder $query): string
    {
       return Str::replaceArray(' ? ', $query->getBindings(), $query->toSql());
    }
}
