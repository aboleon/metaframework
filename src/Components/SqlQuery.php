<?php

declare(strict_types=1);

namespace MetaFramework\Components;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\View\Component;
use MetaFramework\Services\SqlQueryIndexFilterRegistry;
use MetaFramework\Services\SqlQueryService;

class SqlQuery extends Component
{
    public function __construct(
        private readonly Request $request,
        private readonly Router $router,
        private readonly SqlQueryService $sqlQueryService,
        private readonly SqlQueryIndexFilterRegistry $filterRegistry,
    ) {}

    public function shouldRender(): bool
    {
        $user = $this->request->user();

        return $user !== null
            && method_exists($user, 'hasRole')
            && $user->hasRole('dev|super-admin');
    }

    public function render(): Renderable
    {
        $token = (string) $this->request->query('mfw_sql_query', '');

        return view('mfw::components.sql-query')->with([
            'indexImpactAvailable' => $this->filterRegistry->has($this->router->currentRouteName()),
            'result' => $this->sqlQueryService->current($token),
            'returnPath' => $this->returnPath(),
        ]);
    }

    private function returnPath(): string
    {
        $query = $this->request->query();
        unset($query['mfw_sql_query']);

        return $this->request->getPathInfo() . ($query === [] ? '' : '?' . http_build_query($query));
    }
}
