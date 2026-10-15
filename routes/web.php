<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DogController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
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

// Sitemaps: an index plus one file per language (and per chunk of 40 000 URLs), indexable pages only.
Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('sitemap-{locale}-{page}.xml', [SitemapController::class, 'show'])
    ->whereIn('locale', config('app.locales'))->whereNumber('page')->name('sitemap.locale');

/*
 * Every page below exists once per locale: /fr/connexion, /nl/inloggen, /en/login, /de/anmelden…
 * Route names are prefixed with the locale ("nl.login"); use localized_route('login') in code.
 * URL segments come from lang/{locale}/routes.php.
 */
Route::localized(function () {
    Route::get('/', HomeController::class)->name('home');

    // Directory: search, provider profiles, category and city pages (/fr/toiletteur-canin/liege).
    $categorySlugs = implode('|', array_map('preg_quote', __('routes.category_slugs')));
    Route::get(__('routes.search'), SearchController::class)->middleware('throttle:search')->name('search');
    Route::get(__('routes.categories'), [CategoryController::class, 'index'])->name('categories.index');
    Route::get('{category}', [CategoryController::class, 'show'])->where('category', $categorySlugs)
        ->middleware('throttle:search')->name('categories.show');
    Route::get('{category}/{city}', [CategoryController::class, 'city'])
        ->where(['category' => $categorySlugs, 'city' => '[a-z0-9-]+'])->middleware('throttle:search')->name('categories.city');
    Route::get(__('routes.provider').'/{provider:slug}', [ProviderController::class, 'show'])->name('providers.show');
    Route::post(__('routes.provider').'/{provider:slug}/'.__('routes.report'), [ProviderController::class, 'report'])
        ->middleware(['throttle:provider-report', 'antibot'])->name('providers.report');

    // Reviews (publication rules in Review::blockReason, ratings recomputed by the model) and their reports.
    Route::post(__('routes.provider').'/{provider:slug}/'.__('routes.reviews'), [ReviewController::class, 'store'])
        ->middleware(['auth', 'verified', 'throttle:reviews', 'antibot'])->name('reviews.store');
    Route::post(__('routes.reviews').'/{review}/'.__('routes.report'), [ReviewController::class, 'report'])
        ->middleware(['auth', 'verified', 'throttle:review-reports'])->name('reviews.report');
    Route::view(__('routes.reviews_policy'), 'pages.reviews-policy')->name('reviews.policy');

    // "This is my business" and "Add your business": professional account required (visitors go to the pro sign-up).
    Route::middleware('pro')->group(function () {
        Route::get(__('routes.provider').'/{provider:slug}/'.__('routes.claim'), [ClaimController::class, 'create'])->name('claim.create');
        Route::post(__('routes.provider').'/{provider:slug}/'.__('routes.claim'), [ClaimController::class, 'store'])
            ->middleware(['throttle:claims', 'antibot'])->name('claim.store');
        Route::get(__('routes.add_business'), [ClaimController::class, 'createListing'])->name('listing.create');
        Route::post(__('routes.add_business'), [ClaimController::class, 'storeListing'])
            ->middleware(['throttle:claims', 'antibot'])->name('listing.store');
    });

    Route::get(__('routes.contact'), [ContactController::class, 'show'])->name('contact');
    Route::post(__('routes.contact'), [ContactController::class, 'store'])->middleware(['throttle:contact', 'antibot'])->name('contact.store');

    // Pages owned by other work packages: placeholders until their controllers land.
    Route::get(__('routes.guides'), [GuideController::class, 'index'])->name('guides');
    Route::get(__('routes.guides').'/{slug}', [GuideController::class, 'show'])
        ->where('slug', '[a-z0-9-]+')->name('guides.show');
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

        // "My reviews": the author edits or deletes their own reviews.
        Route::get(__('routes.reviews'), [ReviewController::class, 'index'])->name('account.reviews');
        Route::get(__('routes.reviews').'/{review}/'.__('routes.edit'), [ReviewController::class, 'edit'])
            ->can('update', 'review')->name('reviews.edit');
        Route::put(__('routes.reviews').'/{review}', [ReviewController::class, 'update'])
            ->middleware('throttle:reviews')->can('update', 'review')->name('reviews.update');
        Route::delete(__('routes.reviews').'/{review}', [ReviewController::class, 'destroy'])
            ->middleware('throttle:reviews')->can('delete', 'review')->name('reviews.destroy');

        // Professional area: only the listings assigned to the professional (ProviderPolicy::manage).
        Route::middleware('pro')->prefix(__('routes.pro_space'))->group(function () {
            Route::get('/', [ProController::class, 'index'])->name('pro.index');
            Route::get(__('routes.listings').'/{provider}/'.__('routes.edit'), [ProController::class, 'edit'])
                ->can('manage', 'provider')->name('pro.edit');
            Route::put(__('routes.listings').'/{provider}', [ProController::class, 'update'])
                ->middleware('throttle:pro-updates')->can('manage', 'provider')->name('pro.update');
            Route::get(__('routes.listings').'/{provider}/'.__('routes.badge'), [ProController::class, 'createBadgeRequest'])
                ->can('manage', 'provider')->name('pro.badge');
            Route::post(__('routes.listings').'/{provider}/'.__('routes.badge'), [ProController::class, 'storeBadgeRequest'])
                ->middleware('throttle:claims')->can('manage', 'provider')->name('pro.badge.store');
            Route::get(__('routes.listings').'/{provider}/'.__('routes.reviews'), [ProController::class, 'reviews'])
                ->can('manage', 'provider')->name('pro.reviews');
            Route::put(__('routes.reviews').'/{review}/'.__('routes.reply'), [ReviewController::class, 'reply'])
                ->middleware('throttle:pro-updates')->can('reply', 'review')->name('pro.reply');
        });
    });
});

/*
 * Admin area (not localized, language of the browser). Destructive actions need a password typed
 * within the last 15 minutes ("password.recent"); every action is written to admin_actions.
 */
Route::prefix('admin')->name('admin.')->middleware(['locale', 'auth', 'verified', 'can:admin'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('log', [Admin\DashboardController::class, 'log'])->name('log');

    Route::get('claims', [Admin\ClaimController::class, 'index'])->name('claims.index');
    Route::get('claims/{claim}', [Admin\ClaimController::class, 'show'])->name('claims.show');
    Route::post('claims/{claim}/approve', [Admin\ClaimController::class, 'approve'])->name('claims.approve');
    Route::post('claims/{claim}/reject', [Admin\ClaimController::class, 'reject'])->name('claims.reject');

    Route::get('badges', [Admin\BadgeRequestController::class, 'index'])->name('badges.index');
    Route::get('badges/{badgeRequest}', [Admin\BadgeRequestController::class, 'show'])->name('badges.show');
    Route::post('badges/{badgeRequest}/approve', [Admin\BadgeRequestController::class, 'approve'])->name('badges.approve');
    Route::post('badges/{badgeRequest}/reject', [Admin\BadgeRequestController::class, 'reject'])->name('badges.reject');

    Route::get('providers', [Admin\ProviderController::class, 'index'])->name('providers.index');
    Route::get('providers/create', [Admin\ProviderController::class, 'create'])->name('providers.create');
    Route::post('providers', [Admin\ProviderController::class, 'store'])->name('providers.store');
    Route::get('providers/{provider}/edit', [Admin\ProviderController::class, 'edit'])->name('providers.edit');
    Route::put('providers/{provider}', [Admin\ProviderController::class, 'update'])->name('providers.update');
    Route::post('providers/{provider}/toggle', [Admin\ProviderController::class, 'toggle'])->name('providers.toggle');
    Route::delete('providers/{provider}', [Admin\ProviderController::class, 'destroy'])->middleware('password.recent')->name('providers.destroy');

    Route::get('provider-reports', [Admin\ProviderReportController::class, 'index'])->name('provider-reports.index');
    Route::post('provider-reports/{report}/close', [Admin\ProviderReportController::class, 'close'])->name('provider-reports.close');
    Route::post('provider-reports/{report}/apply-removal', [Admin\ProviderReportController::class, 'applyRemoval'])
        ->middleware('password.recent')->name('provider-reports.apply-removal');

    Route::get('reviews', [Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::post('reviews/{review}/hide', [Admin\ReviewController::class, 'hide'])->name('reviews.hide');
    Route::post('reviews/{review}/publish', [Admin\ReviewController::class, 'publish'])->name('reviews.publish');
    Route::delete('reviews/{review}', [Admin\ReviewController::class, 'destroy'])->middleware('password.recent')->name('reviews.destroy');
    Route::post('review-reports/{report}/dismiss', [Admin\ReviewController::class, 'dismissReport'])->name('review-reports.dismiss');

    Route::get('users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::put('users/{user}/role', [Admin\UserController::class, 'updateRole'])->middleware('password.recent')->name('users.role');
    Route::delete('users/{user}', [Admin\UserController::class, 'destroy'])->middleware('password.recent')->name('users.destroy');

    Route::get('messages', [Admin\ContactMessageController::class, 'index'])->name('contact.index');
    Route::post('messages/{message}/handle', [Admin\ContactMessageController::class, 'handle'])->name('contact.handle');

    Route::get('guides', [Admin\PostController::class, 'index'])->name('posts.index');
    Route::post('guides/{post}/unpublish', [Admin\PostController::class, 'unpublish'])->name('posts.unpublish');
});
