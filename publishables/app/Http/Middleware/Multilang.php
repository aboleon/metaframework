<?php declare(strict_types = 1);

namespace App\Http\Middleware;

use Artisan;
use Closure;

class Multilang
{
    public function handle($request, Closure $next)
    {
        if (!session('locale')) {
            session('locale', config('app.fallback_locale'));
        }

        if(!$request->route()->hasParameter('lg')) {
            app()->setLocale(config('app.fallback_locale'));
        }

        if (
            in_array($request->route()->parameter('lg'), config('translatable.active_locales'))
            && $request->route()->parameter('lg') != app()->getLocale()
        )  {
            app()->setLocale($request->route()->parameter('lg'));
        }


        if ($request->filled('lg') && in_array($request->lg, config('translatable.active_locales'))) {
            app()->setLocale($request->lg);
        }

        if (session('locale') != app()->getLocale()) {
            session('locale', app()->getLocale());
            Artisan::call('cache:clear');
        }

        return $next($request);
    }
}
