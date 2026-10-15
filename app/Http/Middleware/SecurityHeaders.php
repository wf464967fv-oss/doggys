<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security headers for every web response.
 *
 * CSP: scripts only from our origin with a per-request nonce (Vite tags get it automatically,
 * Alpine uses its CSP build), OSM tiles for Leaflet maps, Cloudflare Turnstile when configured.
 * Inline <script> tags must carry nonce="{{ Vite::cspNonce() }}".
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        Vite::useCspNonce();

        $response = $next($request);

        $headers = $response->headers;
        $headers->set('Content-Security-Policy', $this->contentSecurityPolicy(Vite::cspNonce()));
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'geolocation=(self), camera=(), microphone=(), payment=(), usb=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        if (app()->isProduction()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function contentSecurityPolicy(string $nonce): string
    {
        $turnstile = config('services.turnstile.site_key') ? ' https://challenges.cloudflare.com' : '';
        $viteDev = Vite::isRunningHot() ? ' '.$this->viteDevServer() : '';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'{$turnstile}{$viteDev}",
            "style-src 'self' 'unsafe-inline'{$viteDev}",
            "img-src 'self' data: https://tile.openstreetmap.org https://*.tile.openstreetmap.org",
            "font-src 'self'{$viteDev}",
            "connect-src 'self'{$viteDev}".($viteDev ? ' '.str_replace('http', 'ws', $this->viteDevServer()) : ''),
            "frame-src 'self'{$turnstile}",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ]);
    }

    private function viteDevServer(): string
    {
        return rtrim(trim((string) file_get_contents(public_path('hot'))), '/');
    }
}
