<?php

declare(strict_types=1);

namespace MetaFramework\Data;

class SqlQueryResult
{
    /**
     * @param  array<int, string>  $columns
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function __construct(
        public readonly string $query,
        public readonly array $columns,
        public readonly array $rows,
        public readonly bool $truncated,
        public readonly bool $affectsIndex,
        public readonly ?string $error = null,
    ) {}

    /**
     * @return array<int, mixed>
     */
    public function column(string $column): array
    {
        return collect($this->rows)
            ->pluck($column)
            ->filter(static fn (mixed $value): bool => $value !== null && $value !== '')
            ->unique()
            ->values()
            ->all();
    }
}
