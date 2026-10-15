<?php

use App\Http\Middleware\EnsureEmailIsVerified;
use App\Http\Middleware\ProtectAgainstBots;
use App\Http\Middleware\RequirePassword;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $middleware->alias([
            'locale' => SetLocale::class,
            'antibot' => ProtectAgainstBots::class,
            'verified' => EnsureEmailIsVerified::class,
            'password.confirm' => RequirePassword::class,
        ]);

        // The locale must be known before auth middleware builds its localized redirects.
        $middleware->prependToPriorityList(AuthenticatesRequests::class, SetLocale::class);

        $middleware->redirectGuestsTo(fn () => localized_route('login'));
        $middleware->redirectUsersTo(fn () => localized_route('account'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Error pages outside a localized route (e.g. unknown URL) still use the language of the URL prefix.
        $exceptions->render(function (HttpException $exception, Request $request) {
            if (in_array($request->segment(1), config('app.locales'), true)) {
                app()->setLocale($request->segment(1));
            }
        });
    })->create();
