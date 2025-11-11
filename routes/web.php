<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AIFlowController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\AIBuilderController;
use App\Http\Controllers\DatabaseController;

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard.getting_started')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Auth scaffolding (Breeze) + Social Logins
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

Route::get('/auth/redirect/{provider}', [SocialAuthController::class, 'redirect'])
    ->where('provider', '^(google|facebook|github)$')
    ->name('oauth.redirect');

Route::get('/auth/callback/{provider}', [SocialAuthController::class, 'callback'])
    ->where('provider', '^(google|facebook|github)$')
    ->name('oauth.callback');

/*
|--------------------------------------------------------------------------
| Protected (needs login)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::prefix('Dashboard')->group(function () {
        Route::get('/GettingStarted', fn () => view('dashboard.getting_started'))->name('dashboard.getting_started');
        Route::get('/Analysis', fn () => view('dashboard.analysis'))->name('dashboard.analysis');
    });

    // General pages
    Route::view('/Blog', 'blog')->name('blog');
    Route::view('/Community', 'community')->name('community');
    Route::view('/CommunityList', 'community_list')->name('community.list');
    Route::view('/Upgrade', 'upgrade')->name('upgrade');

    // Services
    Route::prefix('Services')->group(function () {
        // database tables-only detail page
        Route::get('/Database/{id}', [DatabaseController::class, 'show'])->name('services.database.show');

        // (optional) helpers you already had
        Route::get('/DatabaseAdd', [DatabaseController::class, 'create'])->name('services.database.add');
        Route::post('/Database',   [DatabaseController::class, 'store'])->name('services.database.store');
        Route::post('/Database/suggest', [DatabaseController::class, 'suggest'])->name('services.database.suggest');
        Route::get('/Database/list',     [DatabaseController::class, 'list'])->name('services.database.list');

        Route::view('/Hosting', 'services.hosting')->name('services.hosting');
        Route::view('/Storage', 'services.storage')->name('services.storage');
        Route::view('/StorageFolder', 'services.storage_folder')->name('services.storage.folder');

        Route::get('/Users', [UsersController::class, 'index'])->name('services.users');
    });

    // Account
     Route::prefix('AIBuilder')->group(function () {
        Route::get('/',       [AIBuilderController::class, 'index'])->name('ai.builder');
        Route::post('/plan',  [AIBuilderController::class, 'plan'])->name('ai.builder.plan');
        Route::post('/start', [AIBuilderController::class, 'start'])->name('ai.builder.start');
        // NEW: iframe live preview
        Route::get('/preview/{id}', [AIBuilderController::class, 'preview'])->name('ai.builder.preview.live');
    });

    /*
    |--------------------------------------------------------------------------
    | AI Builder (single canonical place)
    |--------------------------------------------------------------------------
    */
    Route::prefix('AIBuilder')->group(function () {
        Route::get('/',  [AIBuilderController::class, 'index'])->name('ai.builder');
        Route::post('/plan',  [AIBuilderController::class, 'plan'])->name('ai.builder.plan');
        Route::post('/start', [AIBuilderController::class, 'start'])->name('ai.builder.start');
    });
});

/*
|--------------------------------------------------------------------------
| Optional webhook (no auth)
|--------------------------------------------------------------------------
*/
Route::post('/webhooks/ai-builder', [AIBuilderController::class, 'webhook'])->name('ai.builder.webhook');

