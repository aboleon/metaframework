<?php

declare(strict_types=1);

namespace MetaFramework\Services;

use Illuminate\Contracts\Session\Session;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use MetaFramework\Data\SqlQueryResult;

class SqlQueryService
{
    private const MAX_CONTEXTS = 5;

    private const MAX_ROWS = 500;

    /** @var array<string, SqlQueryResult|null> */
    private array $resolvedContexts = [];

    public function __construct(
        private readonly DatabaseManager $database,
        private readonly Session $session,
        private readonly SqlQueryIndexFilterRegistry $filterRegistry,
    ) {}

    public function createContext(string $sql, bool $affectsIndex): string
    {
        $sql = $this->validatedSql($sql);
        $result = $this->execute($sql, $affectsIndex);

        if ($result->error !== null) {
            throw ValidationException::withMessages(['sql_query' => $result->error]);
        }

        $token = Str::random(24);
        $contexts = $this->session->get('mfw.sql_query.contexts', []);
        $contexts[$token] = [
            'query' => $sql,
            'affects_index' => $affectsIndex,
        ];
        $contexts = array_slice($contexts, -self::MAX_CONTEXTS, null, true);
        $this->session->put('mfw.sql_query.contexts', $contexts);

        return $token;
    }

    public function current(?string $token): ?SqlQueryResult
    {
        if (!$token) {
            return null;
        }

        if (array_key_exists($token, $this->resolvedContexts)) {
            return $this->resolvedContexts[$token];
        }

        $context = $this->session->get('mfw.sql_query.contexts.' . $token);

        if (!is_array($context) || !isset($context['query'])) {
            return $this->resolvedContexts[$token] = null;
        }

        return $this->resolvedContexts[$token] = $this->execute(
            (string) $context['query'],
            (bool) ($context['affects_index'] ?? false),
        );
    }

    public function applyToIndex(
        EloquentBuilder|QueryBuilder $query,
        ?string $token,
        ?string $routeName,
    ): EloquentBuilder|QueryBuilder {
        $result = $this->current($token);
        $filter = $this->filterRegistry->for($routeName);

        if (!$result?->affectsIndex || $result->error !== null || !$filter) {
            return $query;
        }

        return $filter->apply($query, $result);
    }

    public function impactsIndex(?string $token, ?string $routeName): bool
    {
        $result = $this->current($token);

        return (bool) $result?->affectsIndex
            && $result->error === null
            && $this->filterRegistry->has($routeName);
    }

    private function execute(string $sql, bool $affectsIndex): SqlQueryResult
    {
        try {
            $rows = $this->database->connection()->select(sprintf(
                'SELECT * FROM (%s) AS mfw_select_query LIMIT %d',
                $sql,
                self::MAX_ROWS + 1,
            ));
        } catch (QueryException $exception) {
            return new SqlQueryResult(
                query: $sql,
                columns: [],
                rows: [],
                truncated: false,
                affectsIndex: $affectsIndex,
                error: __('mfw::sql.query_failed', [
                    'message' => $exception->getPrevious()?->getMessage() ?? $exception->getMessage(),
                ]),
            );
        }

        $truncated = count($rows) > self::MAX_ROWS;
        $rows = array_slice($rows, 0, self::MAX_ROWS);

        return new SqlQueryResult(
            query: $sql,
            columns: empty($rows) ? [] : array_keys((array) $rows[0]),
            rows: array_map(static fn (object $row): array => (array) $row, $rows),
            truncated: $truncated,
            affectsIndex: $affectsIndex,
        );
    }

    private function validatedSql(string $sql): string
    {
        $sql = trim($sql);
        $sql = str_ends_with($sql, ';') ? rtrim(substr($sql, 0, -1)) : $sql;

        $isSelect = preg_match('/\Aselect(?:\s|\()/i', $sql) === 1;
        $hasComment = preg_match('/(?:--|#|\/\*)/', $sql) === 1;
        $hasAdditionalStatement = str_contains($sql, ';');
        $hasWriteClause = preg_match('/\b(?:into\s+(?:out|dump)file|for\s+update|lock\s+in\s+share\s+mode)\b/i', $sql) === 1;

        if (!$isSelect || $hasComment || $hasAdditionalStatement || $hasWriteClause) {
            throw ValidationException::withMessages([
                'sql_query' => __('mfw::sql.select_only'),
            ]);
        }

        return $sql;
    }
}
