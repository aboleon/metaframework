<?php

declare(strict_types=1);

namespace MetaFramework;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use MetaFramework\Accessors\Routing;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The root namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    protected $defer = false;

    protected $namespace = '\MetaFramework\Controllers';

    public const HOME = '/';

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::prefix(Routing::backend())
            ->middleware(['web'])
            ->namespace($this->namespace)
            ->name('mfw.')
            ->group(function () {
                include __DIR__ . '/Routes/web.php';
            });

    }
}
