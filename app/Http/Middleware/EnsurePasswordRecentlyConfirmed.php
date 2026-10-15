<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * "password.recent" middleware for destructive form submissions (POST/DELETE) in the admin area.
 * Laravel's "password.confirm" redirects back to the URL of the request, which cannot be replayed for a
 * form submission: this one sends the admin to the confirmation page and back to the page holding the form.
 */
class EnsurePasswordRecentlyConfirmed
{
    /** A destructive action needs a password typed within the last 15 minutes. */
    public const TIMEOUT_SECONDS = 900;

    public function handle(Request $request, Closure $next): Response
    {
        $confirmedAt = (int) $request->session()->get('auth.password_confirmed_at', 0);

        if (time() - $confirmedAt > self::TIMEOUT_SECONDS) {
            $request->session()->put('url.intended', url()->previous());

            return redirect()->to(localized_route('password.confirm'));
        }

        return $next($request);
    }
}
