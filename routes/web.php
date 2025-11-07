<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\AccountSettingsController;

/*
|------------------------------
| Root: guests -> /login, logged-in -> /Dashboard/GettingStarted
|------------------------------
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect('/Dashboard/GettingStarted')
        : redirect()->route('login');
});

/*
|------------------------------
| Auth scaffolding (Breeze) + Social Logins
|------------------------------
*/
require __DIR__ . '/auth.php';

Route::get('/auth/redirect/{provider}', [SocialAuthController::class, 'redirect'])
    ->where('provider', '^(google|facebook|github)$')
    ->name('oauth.redirect');

Route::get('/auth/callback/{provider}', [SocialAuthController::class, 'callback'])
    ->where('provider', '^(google|facebook|github)$')
    ->name('oauth.callback');

/*
|------------------------------
| PROTECTED APP (admin) — only after login
|------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Optional alias
    Route::redirect('/Home', '/Dashboard/GettingStarted');

    // Blog/Community/Upgrade (agar public chahiye to is block se bahar move kar dena)
    Route::view('/Blog', 'blog');
    Route::view('/Community', 'community');
    Route::view('/CommunityList', 'community_list');
    Route::view('/Upgrade', 'upgrade');

    // Dashboard
    Route::prefix('Dashboard')->group(function () {
        Route::redirect('/', '/Dashboard/GettingStarted');
        Route::view('/GettingStarted', 'dashboard/getting_started');
        Route::view('/Analysis', 'dashboard/analysis');
    });

    // Services
    Route::prefix('Services')->group(function () {
        Route::redirect('/', '/Services/Database');
        Route::view('/Database', 'services/database');
        Route::view('/DatabaseAdd', 'services/database_add');
        Route::view('/DatabaseDetail', 'services/database_detail');
        Route::view('/Hosting', 'services/hosting');
        Route::view('/Storage', 'services/storage');
        Route::view('/StorageFolder', 'services/storage_folder');
        Route::view('/Users', 'services/users');
    });

    // Account
    Route::prefix('Account')->group(function () {
        Route::redirect('/', '/Account/Settings');

        // ❗️IMPORTANT: yeh do routes add karo, aur purana Route::view('/Settings', ...) hata do
        Route::get('/Settings',  [AccountSettingsController::class, 'edit'])->name('account.settings');
        Route::post('/Settings', [AccountSettingsController::class, 'update'])->name('account.settings.update');

        Route::view('/Billing', 'account/billing');
        Route::view('/Security', 'account/security');
    });

    // Support
    Route::prefix('Support')->group(function () {
        Route::redirect('/', '/Support/Docs');
        Route::view('/Docs', 'support/docs');
        Route::view('/DocsDetail', 'support/docs_detail');
        Route::view('/KnowledgeBase', 'support/knowledge_base');
        Route::view('/Tickets', 'support/tickets');
        Route::view('/TicketsDetail', 'support/tickets_detail');
    });
});
