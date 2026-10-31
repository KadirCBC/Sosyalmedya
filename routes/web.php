<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdAdminController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\Web\AdWizardController;
use App\Http\Controllers\Web\FeedWebController;
use App\Http\Controllers\Web\SeoController;
use App\Http\Middleware\EnsureAdminOrModerator;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Kullanıcı Arayüzü
Route::get('/', [FeedWebController::class, 'index'])->name('home');
Route::get('/feed', [FeedWebController::class, 'index'])->name('feed');
Route::get('/posts/{id}', [FeedWebController::class, 'showPost'])->name('posts.show');

// Dinamik SEO ve Sosyal Paylaşım Rotaları
Route::get('/yerel/{citySlug}/{districtSlug}', [SeoController::class, 'localLandingPage'])->name('seo.district');
Route::get('/posts/{id}/og-image', [SeoController::class, 'ogImage'])->name('posts.og-image');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

// Esnaf Self-Service Reklam Sihirbazı Rotaları
Route::prefix('ads')->controller(AdWizardController::class)->group(function () {
    Route::get('create', 'create')->name('ads.create');
    Route::post('/', 'store')->name('ads.store');
    Route::get('calculate-reach', 'calculateReach')->name('ads.calculate-reach');
    Route::get('success/{id}', 'success')->name('ads.success');
    Route::get('mock-paytr-iframe/{id}', 'mockIframe')->name('ads.mock-iframe');
});

// Yönetim ve Moderasyon Paneli Rotaları
Route::prefix('admin')->middleware(['auth', EnsureAdminOrModerator::class])->group(function () {

    // 1. İstatistik ve Özet Panosu
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // 2. Moderasyon Kuyruğu (Süper Admin + Bölge Moderatörleri)
    Route::prefix('moderation')->controller(ModerationController::class)->group(function () {
        Route::get('/', 'index')->name('admin.moderation.index');
        Route::post('{id}/approve', 'approve')->name('admin.moderation.approve');
        Route::post('{id}/hide', 'hide')->name('admin.moderation.hide');
        Route::delete('{id}', 'destroy')->name('admin.moderation.delete');
        Route::post('users/{id}/suspend', 'suspendUser')->name('admin.moderation.suspend-user');
    });

    // 3. Sponsorlu Reklam Yönetimi (Yalnızca Süper Admin)
    Route::prefix('ads')->controller(AdAdminController::class)->middleware(EnsureAdminOrModerator::class . ':super_admin')->group(function () {
        Route::get('/', 'index')->name('admin.ads.index');
        Route::post('{id}/approve', 'approve')->name('admin.ads.approve');
        Route::post('{id}/reject', 'reject')->name('admin.ads.reject');
    });
});
