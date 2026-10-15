<?php

use App\Http\Controllers\Api\ProviderController;
use Illuminate\Support\Facades\Route;

/*
 * Public read-only JSON API for the future mobile app (/api/v1/…). Labels follow ?lang=fr|nl|en|de.
 */
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::middleware('throttle:api')->group(function () {
        Route::get('providers', [ProviderController::class, 'index'])->name('providers.index');
        Route::get('providers/{provider:slug}', [ProviderController::class, 'show'])->name('providers.show');
    });

    // Location autocomplete of the search form (one request per keystroke pause).
    Route::get('places', [ProviderController::class, 'places'])->middleware('throttle:places')->name('places');
});
