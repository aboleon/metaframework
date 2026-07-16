<?php

declare(strict_types=1);

namespace MetaFramework\Controllers;

use Illuminate\Http\RedirectResponse;
use MetaFramework\Http\Requests\ExecuteSqlQueryRequest;
use MetaFramework\Services\SqlQueryService;

class SqlQueryController
{
    public function execute(
        ExecuteSqlQueryRequest $request,
        SqlQueryService $sqlQueryService,
    ): RedirectResponse {
        $token = $sqlQueryService->createContext(
            (string) $request->validated('sql_query'),
            $request->boolean('sql_query_affects_index'),
        );
        $returnPath = (string) $request->validated('return_path');
        $separator = str_contains($returnPath, '?') ? '&' : '?';

        return redirect($returnPath . $separator . http_build_query(['mfw_sql_query' => $token]));
    }
}
