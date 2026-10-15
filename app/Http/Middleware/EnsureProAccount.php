<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * "pro" middleware: professional account with a verified e-mail. Visitors are sent to the professional
 * sign-up form and come back to the requested page afterwards; owner accounts get an explanation page.
 */
class EnsureProAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(localized_route('register', ['type' => 'pro']));
        }

        if (! $user->hasVerifiedEmail()) {
            return redirect()->guest(localized_route('verification.notice'));
        }

        if (! $user->isPro()) {
            return response()->view('pro.not-pro', [], 403);
        }

        return $next($request);
    }
}
