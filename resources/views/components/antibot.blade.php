{{--
    Anti-bot fields for public forms; the route must use the "antibot" middleware.
    Honeypot input (hidden from people and assistive tech) + Cloudflare Turnstile widget when configured.
--}}
<div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
    <label for="{{ \App\Http\Middleware\ProtectAgainstBots::HONEYPOT_FIELD }}">{{ __('ui.honeypot_label') }}</label>
    <input type="text" id="{{ \App\Http\Middleware\ProtectAgainstBots::HONEYPOT_FIELD }}" name="{{ \App\Http\Middleware\ProtectAgainstBots::HONEYPOT_FIELD }}" value="" tabindex="-1" autocomplete="off">
</div>

@if (config('services.turnstile.enabled'))
    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-language="{{ app()->getLocale() }}" data-theme="light"></div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}"></script>
    @endonce
@endif

@error('form')
    <x-alert variant="error">{{ $message }}</x-alert>
@enderror
