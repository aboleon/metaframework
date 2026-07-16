<?php

declare(strict_types=1);

namespace MetaFramework\Services;

use Illuminate\Contracts\Container\Container;
use MetaFramework\Contracts\SqlQueryIndexFilter;

class SqlQueryIndexFilterRegistry
{
    /** @var array<string, class-string<SqlQueryIndexFilter>> */
    private array $filters = [];

    public function __construct(private readonly Container $container) {}

    /**
     * @param  class-string<SqlQueryIndexFilter>  $filter
     */
    public function register(string $routeName, string $filter): void
    {
        $this->filters[$routeName] = $filter;
    }

    public function has(?string $routeName): bool
    {
        return $routeName !== null && isset($this->filters[$routeName]);
    }

    public function for(?string $routeName): ?SqlQueryIndexFilter
    {
        if (!$this->has($routeName)) {
            return null;
        }

        return $this->container->make($this->filters[$routeName]);
    }
}
