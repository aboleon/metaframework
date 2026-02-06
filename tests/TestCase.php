<?php

declare(strict_types=1);

namespace Tests;

use MetaFramework\ServiceProvider as MetaFrameworkServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Spatie\Translatable\TranslatableServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            TranslatableServiceProvider::class,
            MetaFrameworkServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('app.locale', 'fr');
        $app['config']->set('app.fallback_locale', 'bg');
        $app['config']->set('mfw.translatable.multilang', true);
        $app['config']->set('mfw.translatable.locales', ['fr', 'bg', 'en']);
        $app['config']->set('mfw.translatable.active_locales', ['fr', 'bg']);
    }
}
