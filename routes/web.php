<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DogController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\SearchController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;

/*
 * "/" sends visitors to their browser language (Accept-Language), French otherwise.
 */
Route::get('/', function (Request $request) {
    $locale = $request->getPreferredLanguage(config('app.locales')) ?? config('app.locales')[0];

    return redirect()->route($locale.'.home')->setVary('Accept-Language');
})->name('root');

/*
 * Every page below exists once per locale: /fr/connexion, /nl/inloggen, /en/login, /de/anmelden…
 * Route names are prefixed with the locale ("nl.login"); use localized_route('login') in code.
 * URL segments come from lang/{locale}/routes.php.
 */
Route::localized(function () {
    Route::get('/', HomeController::class)->name('home');

    // Directory: search, provider profiles, category and city pages (/fr/toiletteur-salon/liege).
    $categorySlugs = implode('|', array_map('preg_quote', __('routes.category_slugs')));
    Route::get(__('routes.search'), SearchController::class)->name('search');
    Route::get(__('routes.categories'), [CategoryController::class, 'index'])->name('categories.index');
    Route::get('{category}', [CategoryController::class, 'show'])->where('category', $categorySlugs)->name('categories.show');
    Route::get('{category}/{city}', [CategoryController::class, 'city'])
        ->where(['category' => $categorySlugs, 'city' => '[a-z0-9-]+'])->name('categories.city');
    Route::get(__('routes.provider').'/{provider:slug}', [ProviderController::class, 'show'])->name('providers.show');
    Route::post(__('routes.provider').'/{provider:slug}/'.__('routes.report'), [ProviderController::class, 'report'])
        ->middleware(['throttle:5,60', 'antibot'])->name('providers.report');

    // Pages owned by other work packages: placeholders until their controllers land.
    Route::view(__('routes.provider').'/{provider}/'.__('routes.claim'), 'pages.coming-soon')->name('claim.create');
    Route::get(__('routes.guides'), [GuideController::class, 'index'])->name('guides');
    Route::get(__('routes.guides').'/{slug}', [GuideController::class, 'show'])
        ->where('slug', '[a-z0-9-]+')->name('guides.show');
    Route::view(__('routes.contact'), 'pages.coming-soon')->name('contact');
    Route::view(__('routes.reviews_policy'), 'pages.coming-soon')->name('reviews.policy');
    Route::view(__('routes.legal_notice'), 'pages.coming-soon')->name('legal.notice');
    Route::view(__('routes.privacy'), 'pages.coming-soon')->name('legal.privacy');
    Route::view(__('routes.terms'), 'pages.coming-soon')->name('legal.terms');
    Route::view(__('routes.cookies'), 'pages.coming-soon')->name('legal.cookies');

    // Authentication (Laravel Fortify controllers, localized routes).
    Route::middleware('guest')->group(function () {
        Route::get(__('routes.login'), [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post(__('routes.login'), [AuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:login')->name('login.store');

        Route::get(__('routes.register'), [RegisteredUserController::class, 'create'])->name('register');
        Route::post(__('routes.register'), [RegisteredUserController::class, 'store'])
            ->middleware(['throttle:register', 'antibot'])->name('register.store');

        Route::get(__('routes.forgot_password'), [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post(__('routes.forgot_password'), [PasswordResetLinkController::class, 'store'])
            ->middleware('throttle:password-reset')->name('password.email');
        Route::get(__('routes.reset_password').'/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post(__('routes.reset_password'), [NewPasswordController::class, 'store'])
            ->middleware('throttle:password-reset')->name('password.update');
    });

    Route::middleware('auth')->group(function () {
        Route::post(__('routes.logout'), [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get(__('routes.verify_email'), EmailVerificationPromptController::class)->name('verification.notice');
        Route::post(__('routes.verify_email'), [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')->name('verification.send');
        Route::get(__('routes.verify_email').'/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

        Route::get(__('routes.confirm_password'), [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
        Route::post(__('routes.confirm_password'), [ConfirmablePasswordController::class, 'store'])
            ->middleware('throttle:6,1')->name('password.confirm.store');
    });

    // Account settings and GDPR rights: signed in is enough (an unverified or mistyped e-mail never locks them).
    Route::middleware('auth')->prefix(__('routes.account'))->group(function () {
        Route::get(__('routes.profile'), [AccountController::class, 'profile'])->name('account.profile');
        Route::put(__('routes.profile'), [ProfileInformationController::class, 'update'])
            ->middleware('throttle:account-sensitive')->name('account.profile.update');
        Route::get(__('routes.verify_email').'/{id}/{hash}', [AccountController::class, 'confirmEmail'])
            ->middleware(['signed', 'throttle:6,1'])->name('account.email.verify');
        Route::put(__('routes.password'), [PasswordController::class, 'update'])
            ->middleware('throttle:account-sensitive')->name('account.password.update');
        Route::get(__('routes.my_data'), [AccountController::class, 'export'])
            ->middleware(['password.confirm', 'throttle:account-sensitive'])->name('account.export');
        Route::delete('/', [AccountController::class, 'destroy'])
            ->middleware('throttle:account-sensitive')->name('account.destroy');
    });

    // Account area (all roles; e-mail must be verified).
    Route::middleware(['auth', 'verified'])->prefix(__('routes.account'))->group(function () {
        Route::get('/', [AccountController::class, 'dashboard'])->name('account');

        Route::get(__('routes.dogs').'/'.__('routes.create'), [DogController::class, 'create'])->name('dogs.create');
        Route::post(__('routes.dogs'), [DogController::class, 'store'])->middleware('throttle:dogs')->name('dogs.store');
        Route::get(__('routes.dogs').'/{dog}/'.__('routes.edit'), [DogController::class, 'edit'])
            ->can('update', 'dog')->name('dogs.edit');
        Route::put(__('routes.dogs').'/{dog}', [DogController::class, 'update'])
            ->middleware('throttle:dogs')->can('update', 'dog')->name('dogs.update');
        Route::delete(__('routes.dogs').'/{dog}', [DogController::class, 'destroy'])
            ->middleware('throttle:dogs')->can('delete', 'dog')->name('dogs.destroy');
    });
});
