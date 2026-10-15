<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified as BaseMiddleware;
use Symfony\Component\HttpFoundation\Response;

/**
 * "verified" middleware redirecting to the e-mail verification notice of the current locale.
 */
class EnsureEmailIsVerified extends BaseMiddleware
{
    /**
     * @param  string|null  $redirectToRoute
     */
    public function handle($request, Closure $next, $redirectToRoute = null): Response
    {
        return parent::handle($request, $next, $redirectToRoute ?? app()->getLocale().'.verification.notice');
    }
}
