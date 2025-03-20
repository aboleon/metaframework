<?php

namespace App\Providers;

use Illuminate\Support\Facades\{
    Blade,
    Cache,
};
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('path.public', function () {
            return config('app.public_path');
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        Password::defaults(function () {
            return Password::min(8);
        });

        Blade::directive('role', function ($arguments) {
            return "<?php if (auth()->check() && auth()->user()->hasRole({$arguments})) { ?>";
        });
        Blade::directive('endrole', function () {
            return "<?php } ?>";
        });

        Cache::rememberForever('multilang', fn() => config('translatable.multilang'));


    }
}
