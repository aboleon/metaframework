<?php

declare(strict_types=1);

namespace MetaFramework;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use MetaFramework\Components\TranslatableTabs;
use MetaFramework\Console\Install;
use MetaFramework\Polyglote\Events\TranslationHasBeenSetEvent as MetaTranslationHasBeenSetEvent;
use MetaFramework\Polyglote\Translatable as MetaTranslatable;
use MetaFramework\Services\SqlQueryIndexFilterRegistry;
use MetaFramework\Services\SqlQueryService;
use Spatie\Translatable\Events\TranslationHasBeenSetEvent as SpatieTranslationHasBeenSetEvent;
use Spatie\Translatable\Translatable as SpatieTranslatable;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MetaTranslatable::class, static fn () => new MetaTranslatable);
        $this->app->bind(SpatieTranslatable::class, MetaTranslatable::class);
        $this->app->bind('translatable', SpatieTranslatable::class);
        $this->app->singleton(SqlQueryIndexFilterRegistry::class);
        $this->app->scoped(SqlQueryService::class);
    }

    public function boot(): void
    {
        Event::listen(SpatieTranslationHasBeenSetEvent::class, static function (SpatieTranslationHasBeenSetEvent $event): void {
            event(new MetaTranslationHasBeenSetEvent(
                $event->model,
                $event->key,
                $event->locale,
                $event->oldValue,
                $event->newValue,
            ));
        });

        Blade::if('role', static function ($arguments): bool {
            $user = auth()->user();

            return (bool) $user
                && method_exists($user, 'hasRole')
                && $user->hasRole($arguments);
        });

        $this->loadViewsFrom(__DIR__.'/Resources/views', 'mfw');
        $this->loadTranslationsFrom(__DIR__.'/Resources/lang', 'mfw');
        Blade::componentNamespace('MetaFramework\Components', 'mfw');
        Blade::component(TranslatableTabs::class, 'mfw-translatables');

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
            __DIR__.'/../publishables/config/' => config_path(),
            __DIR__.'/../publishables/public/' => public_path(),
            __DIR__.'/../publishables/lang/' => base_path('lang'),
            __DIR__.'/../publishables/database/' => database_path(),
            __DIR__.'/../publishables/resources/' => resource_path(),
        ], 'mfw-install');
    }

    /**
     * Publishes the Auth package
     */
    private function publishAuth(): void
    {
        $this->publishes([
            __DIR__.'/../publishables/auth/' => base_path(),
        ], 'mfw-auth');
    }

    private function publishAssets(): void
    {
        $this->publishes([
            __DIR__.'/../publishables/public/vendor/' => public_path('vendor/'),
        ], 'mfw-assets');
    }

    private function publishLang(): void
    {
        $this->publishes([
            __DIR__.'/../publishables/lang/' => base_path('lang'),
        ], 'mfw-lang');
    }

    private function publishViews(): void
    {
        $this->publishes([
            __DIR__.'/Resources/views/nav/' => resource_path('views/vendor/mfw/nav'),
        ], 'mfw-views');
    }
}
