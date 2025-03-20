<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;

class Localization
{
    public function handle(Request $request, Closure $next): Response
    {
        $activeLocales = config('mfw.translatable.active_locales');

        $locale = $request->segment(1);

        if (in_array($locale, $activeLocales)) {
            app()->setLocale($locale);
            URL::defaults(['locale' => $locale]);
        } else {
            $fallbackLocale = config('mfw.translatable.fallback_locale');
            return redirect()->to($this->replaceLocaleInUrl($request->fullUrl(), $fallbackLocale));
        }

        return $next($request);
    }

    /**
     * Replace the locale in the given URL with the fallback locale.
     *
     * @param string $url
     * @param string $fallbackLocale
     * @return string
     */
    protected function replaceLocaleInUrl(string $url, string $fallbackLocale): string
    {
        $parsedUrl = parse_url($url);
        $segments = explode('/', ltrim($parsedUrl['path'], '/'));

        if (isset($segments[0])) {
            $segments[0] = $fallbackLocale;
        }

        $updatedPath = '/' . implode('/', $segments);
        $updatedUrl = ($parsedUrl['scheme'] ?? 'http') . '://' . $parsedUrl['host'] . $updatedPath;

        if (isset($parsedUrl['query'])) {
            $updatedUrl .= '?' . $parsedUrl['query'];
        }

        return $updatedUrl;
    }
}
