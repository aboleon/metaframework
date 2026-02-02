<?php

namespace MetaFramework;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\{
    App,
    Blade,
    View};
use MetaFramework\Facades\MetaFacade;
use MetaFramework\Facades\NavFacade;
use MetaFramework\Models\Meta;
use MetaFramework\Models\Nav;
use MetaFramework\Console\Install;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {

    }

    public function boot(): void
    {
        Blade::directive('role', function ($arguments) {
            return "<?php if (auth()->check() && auth()->user()->hasRole({$arguments})) { ?>";
        });
        Blade::directive('endrole', function () {
            return "<?php } ?>";
        });

        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'mfw');
        Blade::componentNamespace('MetaFramework\Components', 'mfw');

        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');

        View::share('current_locale', App::getLocale());

        Paginator::useBootstrapFive();

        $this->publishInstall();
        $this->publishAuth();
        $this->publishAssets();
        $this->publishLang();
        $this->publishViews();

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
            __DIR__ . '/../publishables/resources/' => resource_path(),
        ], 'mfw-install');
    }

    /**
     * Publishes the Auth package
     * @return void
     */
    private function publishAuth(): void
    {
        $this->publishes([
            __DIR__ . '/../publishables/auth/' => base_path(),
        ], 'mfw-auth');
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

    private function publishViews(): void
    {
        $this->publishes([
            __DIR__ . '/../publishables/resources/views/nav/' => resource_path('views/vendor/mfw/nav'),
        ], 'mfw-views');
    }
}
