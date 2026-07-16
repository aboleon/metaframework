<?php

declare(strict_types=1);

namespace MetaFramework\Contracts;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use MetaFramework\Data\SqlQueryResult;

interface SqlQueryIndexFilter
{
    public function apply(
        EloquentBuilder|QueryBuilder $query,
        SqlQueryResult $result,
    ): EloquentBuilder|QueryBuilder;
}
