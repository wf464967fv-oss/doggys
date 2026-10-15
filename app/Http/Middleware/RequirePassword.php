<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\RequirePassword as BaseMiddleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * "password.confirm" middleware redirecting to the password confirmation page of the current locale.
 */
class RequirePassword extends BaseMiddleware
{
    /**
     * @param  Request  $request
     * @param  string|null  $redirectToRoute
     * @param  string|int|null  $passwordTimeoutSeconds
     */
    public function handle($request, Closure $next, $redirectToRoute = null, $passwordTimeoutSeconds = null): Response
    {
        return parent::handle($request, $next, $redirectToRoute ?? app()->getLocale().'.password.confirm', $passwordTimeoutSeconds);
    }
}
