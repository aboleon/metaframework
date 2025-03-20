<?php

namespace MetaFramework;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\{
    App,
    Blade,
    View};
use MetaFramework\Facades\MetaFacade;
use MetaFramework\Facades\NavFacade;
use MetaFramework\Mediaclass\Facades\MediaclassFacade;
use MetaFramework\Mediaclass\Mediaclass;
use MetaFramework\Models\Meta;
use MetaFramework\Models\Nav;
use MetaFramework\Console\Install;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        /**
         * Façades
         */
        $this->app->singleton('nav', fn($app) => new Nav());
        $this->app->singleton('meta', fn($app) => new Meta());
        $this->app->singleton('mediaclass', fn($app) => new Mediaclass());

        $this->app->bind('MetaFramework\Facades\NavFacade', fn($app) => new NavFacade());
        $this->app->bind('MetaFramework\Facades\MetaFacade', fn($app) => new MetaFacade());
        $this->app->bind('MetaFramework\Mediaclass\Facades\MediaclassFacade', fn($app) => new MediaclassFacade());
    }

    public function boot(): void
    {
        // Load views, routes, and other resources
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'mfw');
        Blade::componentNamespace('\MetaFramework\\Components', 'mfw');

        $this->loadViewsFrom(__DIR__ . '/Mediaclass/Views', 'mediaclass');
        Blade::componentNamespace('MetaFramework\Mediaclass\\Components', 'mediaclass');

        $this->loadRoutesFrom(__DIR__.'/Mediaclass/Routes/public.php');
        $this->loadRoutesFrom(__DIR__.'/Mediaclass/Routes/panel.php');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');

        View::share('current_locale', App::getLocale());

        Paginator::useBootstrapFive();

        // Publish assets, configurations, and other resources
        $this->publishInstall();
        $this->publishAssets();
        $this->publishLang();
        $this->publishMediaclass();

        // Register the custom Artisan command
        if ($this->app->runningInConsole()) {
            $this->commands([
                Install::class,
            ]);
        }
    }

    private function publishInstall(): void
    {
        $this->publishes([
            __DIR__ . '/../publishables/config/' => config_path(),
            __DIR__ . '/../publishables/public/' => public_path(),
            __DIR__ . '/../publishables/lang/' => base_path('lang'),
            __DIR__ . '/../publishables/database/' => database_path(),
           // __DIR__ . '/../publishables/app/' => app_path(),
            __DIR__ . '/../publishables/resources/' => resource_path(),
          //  __DIR__ . '/../publishables/routes/' => base_path('routes'),
        ], 'mfw-install');
    }

    private function publishAssets(): void
    {
        $this->publishes([
            __DIR__ . '/../publishables/public/vendor/' => public_path('vendor/'),
        ], 'mfw-assets');
    }

    private function publishLang(): void
    {
        $this->publishes([
            __DIR__ . '/../publishables/lang/' => base_path('lang'),
        ], 'mfw-lang');
    }

    private function publishMediaclass(): void
    {
        $this->publishes([
            __DIR__ . '/../publishables/public/vendor/mfw/mediaclass/' => public_path('vendor/mfw/mediaclass/'),
            __DIR__ . '/../publishables/lang/fr/mediaclass.php' => base_path('lang/fr/mediaclass.php'),
            __DIR__ . '/../publishables/lang/en/mediaclass.php' => base_path('lang/en/mediaclass.php'),
        ], 'mfw-mediaclass');
    }
}