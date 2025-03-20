<?php

namespace App\Http\Middleware;

use Closure;

class Roles
{
    public function handle($request, Closure $next, $roles)
    {
        if (is_null($request->user())) {
            return redirect()->route('account.auth')->with('bad_access', 'must_authenticate');
        }

        if (!$request->user()->hasRole($roles)) {
            return redirect('/')->with('bad_access', 'role');
        }
        return $next($request);
    }
}
