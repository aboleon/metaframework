<?php

declare(strict_types=1);

namespace Tests\Unit\Components;

use Illuminate\Http\Request;
use Illuminate\Support\ViewErrorBag;
use MetaFramework\Components\SqlQuery;
use MetaFramework\Inputable\InputableServiceProvider;
use MetaFramework\Services\SqlQueryIndexFilterRegistry;
use MetaFramework\Services\SqlQueryService;
use Tests\TestCase;

class SqlQueryTest extends TestCase
{
    public function test_it_renders_a_topbar_toggle_and_collapsible_query_panel_for_privileged_users(): void
    {
        $this->app->register(InputableServiceProvider::class);
        $request = Request::create('/panel/users', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $request->setUserResolver(static fn () => new class
        {
            public function hasRole(string $roles): bool
            {
                return $roles === 'dev|super-admin';
            }
        });
        view()->share('errors', new ViewErrorBag);

        $component = new SqlQuery(
            $request,
            $this->app['router'],
            $this->app->make(SqlQueryService::class),
            $this->app->make(SqlQueryIndexFilterRegistry::class),
        );

        $this->assertTrue($component->shouldRender());

        $html = $component->render()->render();
        $source = file_get_contents(__DIR__ . '/../../../src/Resources/views/components/sql-query.blade.php');

        $this->assertStringContainsString('id="mfw-sql-query-toggle"', $html);
        $this->assertStringContainsString('id="mfw-sql-query-panel"', $html);
        $this->assertStringContainsString('name="sql_query"', $html);
        $this->assertStringContainsString('name="sql_query_affects_index"', $html);
        $this->assertIsString($source);
        $this->assertStringContainsString('slideToggle(180', $source);
        $this->assertStringContainsString('slideUp(180', $source);
    }
}
