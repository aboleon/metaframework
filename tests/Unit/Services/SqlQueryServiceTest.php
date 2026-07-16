<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use MetaFramework\Contracts\SqlQueryIndexFilter;
use MetaFramework\Data\SqlQueryResult;
use MetaFramework\Services\SqlQueryIndexFilterRegistry;
use MetaFramework\Services\SqlQueryService;
use Tests\TestCase;

class SqlQueryServiceTest extends TestCase
{
    public function test_it_executes_and_restores_a_select_query_context(): void
    {
        $service = $this->app->make(SqlQueryService::class);
        $token = $service->createContext('SELECT 42 AS answer;', false);

        $result = $service->current($token);

        $this->assertNotNull($result);
        $this->assertSame(['answer'], $result->columns);
        $this->assertSame(42, $result->rows[0]['answer']);
        $this->assertFalse($result->truncated);
        $this->assertFalse($result->affectsIndex);
    }

    public function test_it_rejects_non_select_and_multiple_statements(): void
    {
        $service = $this->app->make(SqlQueryService::class);

        foreach (['DELETE FROM users', 'SELECT 1; SELECT 2'] as $query) {
            try {
                $service->createContext($query, false);
                $this->fail('The unsafe SQL query should have been rejected.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('sql_query', $exception->errors());
            }
        }
    }

    public function test_registered_filter_applies_to_query_builders_used_by_indexes_or_datatables(): void
    {
        $this->app->make(SqlQueryIndexFilterRegistry::class)
            ->register('test.records.index', TestSqlQueryIndexFilter::class);
        $service = $this->app->make(SqlQueryService::class);
        $token = $service->createContext('SELECT 7 AS id', true);

        $query = $service->applyToIndex(
            DB::table('records'),
            $token,
            'test.records.index',
        );

        $this->assertStringContainsString('"records"."id" in (?)', $query->toSql());
        $this->assertSame([7], $query->getBindings());
    }
}

class TestSqlQueryIndexFilter implements SqlQueryIndexFilter
{
    public function apply(
        EloquentBuilder|QueryBuilder $query,
        SqlQueryResult $result,
    ): EloquentBuilder|QueryBuilder {
        return $query->whereIn('records.id', $result->column('id'));
    }
}
