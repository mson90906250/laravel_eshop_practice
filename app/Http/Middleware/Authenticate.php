<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {

            if (preg_match('/\/admin\/*/i', $request->getPathInfo())) {

                return route('admin.login.showLoginForm');

            }

            return route('login.showLoginForm');

        }
    }
}
