<?php

namespace App\Providers;

use App\Models\User;
use Closure;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Absolute URLs (canonical, hreflang, e-mails) always use APP_URL, never the request Host header.
        if (! $this->app->isLocal()) {
            URL::forceRootUrl(Config::get('app.url'));
            URL::forceScheme((string) parse_url((string) Config::get('app.url'), PHP_URL_SCHEME) ?: 'https');
        }

        $this->registerLocalizedRoutesMacro();
        $this->configureAuthentication();

        Gate::define('admin', fn (User $user): bool => $user->isAdmin());
    }

    /**
     * Route::localized(fn () => ...) registers the given routes once per locale, under the URL prefix
     * "/{locale}", the name prefix "{locale}." and the "locale:{locale}" middleware.
     * While the closure runs, the app locale is the one being registered, so __('routes.x') yields
     * the translated URL segment.
     */
    private function registerLocalizedRoutesMacro(): void
    {
        Route::macro('localized', function (Closure $routes): void {
            $current = app()->getLocale();

            foreach (Config::get('app.locales') as $locale) {
                app()->setLocale($locale);

                Route::prefix($locale)
                    ->name($locale.'.')
                    ->middleware('locale:'.$locale)
                    ->group($routes);
            }

            app()->setLocale($current);
        });
    }

    private function configureAuthentication(): void
    {
        Password::defaults(function () {
            $rule = Password::min(10)->letters()->mixedCase()->numbers()->max(255);

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });

        RateLimiter::for('register', fn (Request $request) => Limit::perHour(10)->by($request->ip()));
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        // Actions checking the current password (profile, password, export, deletion): no guessing with a stolen session.
        RateLimiter::for('account-sensitive', fn (Request $request) => Limit::perMinute(6)
            ->by($request->user()?->id.'|'.$request->route()?->getName()));
        RateLimiter::for('dogs', fn (Request $request) => Limit::perMinute(30)->by($request->user()?->id ?: $request->ip()));

        // One counter per use (the key is prefixed), so a busy form never eats the budget of another one.
        RateLimiter::for('reviews', fn (Request $request) => Limit::perMinute(10)->by('reviews|'.($request->user()?->id ?: $request->ip())));
        RateLimiter::for('review-reports', fn (Request $request) => Limit::perHour(20)->by('review-reports|'.($request->user()?->id ?: $request->ip())));
        RateLimiter::for('claims', fn (Request $request) => Limit::perHour(10)->by('claims|'.($request->user()?->id ?: $request->ip())));
        RateLimiter::for('pro-updates', fn (Request $request) => Limit::perMinute(30)->by('pro-updates|'.($request->user()?->id ?: $request->ip())));
        RateLimiter::for('contact', fn (Request $request) => [
            Limit::perHour(5)->by('contact|'.$request->ip()),
            Limit::perDay(200)->by('contact|global'),
        ]);

        // Directory: search pages and API are cheap per request but must not be hammered from one IP.
        // Category and city pages get more room than the search form: crawlers read them in bursts.
        RateLimiter::for('search', fn (Request $request) => $request->routeIs('*.search')
            ? Limit::perMinute(30)->by('search|'.$request->ip())
            : Limit::perMinute(120)->by('directory-pages|'.$request->ip()));
        RateLimiter::for('provider-report', fn (Request $request) => Limit::perHour(5)->by('provider-report|'.$request->ip()));
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by('api|'.$request->ip()));
        RateLimiter::for('places', fn (Request $request) => Limit::perMinute(120)->by('places|'.$request->ip()));

        // Links in auth e-mails point to the pages of the language the user was browsing in.
        VerifyEmail::createUrlUsing(fn (User $user): string => URL::temporarySignedRoute(
            app()->getLocale().'.verification.verify',
            now()->addMinutes(Config::get('auth.verification.expire', 60)),
            ['id' => $user->getKey(), 'hash' => sha1($user->getEmailForVerification())],
        ));

        VerifyEmail::toMailUsing(fn (User $user, string $url): MailMessage => (new MailMessage)
            ->subject(__('mail.verify.subject'))
            ->greeting(__('mail.greeting', ['name' => $user->name]))
            ->line(__('mail.verify.intro'))
            ->action(__('mail.verify.action'), $url)
            ->line(__('mail.verify.outro'))
            ->salutation(__('mail.salutation')));

        ResetPassword::toMailUsing(fn (User $user, string $token): MailMessage => (new MailMessage)
            ->subject(__('mail.reset.subject'))
            ->greeting(__('mail.greeting', ['name' => $user->name]))
            ->line(__('mail.reset.intro'))
            ->action(__('mail.reset.action'), localized_route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]))
            ->line(__('mail.reset.expire', ['count' => Config::get('auth.passwords.users.expire')]))
            ->line(__('mail.reset.outro'))
            ->salutation(__('mail.salutation')));
    }
}
