<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Anti-bot guard for public forms (alias "antibot"); pair it with <x-antibot /> in the form.
 *
 * - Honeypot: the hidden "website_url" field must stay empty.
 * - Cloudflare Turnstile: verified only when both TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY are set
 *   (config services.turnstile.enabled, also used by the widget and the CSP).
 */
class ProtectAgainstBots
{
    public const HONEYPOT_FIELD = 'website_url';

    public function handle(Request $request, Closure $next): Response
    {
        if (filled($request->input(self::HONEYPOT_FIELD))) {
            throw ValidationException::withMessages(['form' => __('ui.antibot_failed')]);
        }

        if (config('services.turnstile.enabled') && ! $this->passesTurnstile($request)) {
            throw ValidationException::withMessages(['form' => __('ui.antibot_failed')]);
        }

        return $next($request);
    }

    private function passesTurnstile(Request $request): bool
    {
        $token = $request->input('cf-turnstile-response');

        if (! is_string($token) || $token === '') {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $request->ip(),
            ]);
        } catch (ConnectionException) {
            return false;
        }

        return $response->successful() && $response->json('success') === true;
    }
}
