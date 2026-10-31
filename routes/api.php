<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\BusinessProfileController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\EmergencyAlertController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\LeaderboardController;
use App\Http\Controllers\Api\MarketplaceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\PushSubscriptionController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Middleware\IpSpamDefenseMiddleware;
use App\Http\Middleware\ThrottleInteractions;
use App\Http\Middleware\ThrottlePostCreation;
use App\Http\Middleware\TrustCloudflareProxies;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sosyal Medya Platformu (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware([TrustCloudflareProxies::class, IpSpamDefenseMiddleware::class])->group(function () {

    // Kimlik Doğrulama Rotaları
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('send-otp', 'sendOtp');
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::post('login-otp', 'loginWithOtp');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('me', 'me');
            Route::post('logout', 'logout');
        });
    });

    // Çift Akış (Feed) Rotaları
    Route::prefix('feed')->controller(FeedController::class)->group(function () {
        Route::get('general', 'general');
        Route::get('local', 'local');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('following', 'following');
        });
    });

    // Yerel İtibar & Haftanın Komşuları Liderlik Tablosu
    Route::get('locations/{locationId}/leaderboard', [LeaderboardController::class, 'index'])->name('locations.leaderboard');

    // Mahalle Pazarı (Marketplace) Rotaları
    Route::prefix('marketplace')->controller(MarketplaceController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/carousel', 'carousel');
        Route::get('/{id}', 'show');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', 'store');
            Route::post('/{id}/status', 'updateStatus');
        });
    });

    // Güvenli Sohbet / Mesajlaşma Rotaları
    Route::prefix('chat')->controller(ChatController::class)->middleware('auth:sanctum')->group(function () {
        Route::get('conversations', 'conversations');
        Route::post('start/{itemId}', 'start');
        Route::get('conversations/{conversationId}/messages', 'messages');
        Route::post('conversations/{conversationId}/messages', 'sendMessage');
    });

    // Acil Durum Ağı (Kırmızı Akış) Rotaları
    Route::prefix('emergency')->controller(EmergencyAlertController::class)->group(function () {
        Route::get('alerts', 'index');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('alerts', 'store');
            Route::post('alerts/{id}/verify', 'verify');
            Route::post('alerts/{id}/resolve', 'resolve');
        });
    });

    // Esnaf İşletme Sayfası (Business Profiles) Rotaları
    Route::prefix('business')->controller(BusinessProfileController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('/{id}', 'show');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', 'store');
            Route::post('/{id}/catalog', 'addCatalogItem');
            Route::delete('/catalog/{itemId}', 'deleteCatalogItem');
            Route::post('/{id}/reviews', 'addReview');
            Route::post('/{id}/subscribe', 'subscribe');
        });
    });

    // Gönderi (Post) ve Etkileşim Rotaları
    Route::prefix('posts')->controller(PostController::class)->group(function () {
        Route::get('{id}', 'show');

        Route::middleware('auth:sanctum')->group(function () {
            // Gönderi Paylaşma: Dakikada max 3 gönderi
            Route::post('/', 'store')->middleware(ThrottlePostCreation::class);
            // Beğeni: Dakikada max 20 etkileşim
            Route::post('{id}/like', 'toggleLike')->middleware(ThrottleInteractions::class);
        });
    });

    // Yorum (Comment) Rotaları
    Route::prefix('posts/{postId}/comments')->controller(CommentController::class)->group(function () {
        Route::get('/', 'index');

        Route::middleware('auth:sanctum')->group(function () {
            // Yorum Yapma: Dakikada max 20 etkileşim
            Route::post('/', 'store')->middleware(ThrottleInteractions::class);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::delete('comments/{id}', [CommentController::class, 'destroy']);
    });

    // Bildirim (Notification) Rotaları
    Route::prefix('notifications')->controller(NotificationController::class)->middleware('auth:sanctum')->group(function () {
        Route::get('/', 'index');
        Route::get('stream', 'stream'); // Server-Sent Events (SSE) anlık bildirim kanalı
        Route::post('{id}/read', 'markAsRead');
        Route::post('read-all', 'markAllAsRead');
    });

    // WebPush ve PWA Bildirim Abonelik Rotaları
    Route::prefix('push')->controller(PushSubscriptionController::class)->group(function () {
        Route::get('vapid-public-key', 'getVapidPublicKey');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('subscribe', 'subscribe');
            Route::post('unsubscribe', 'unsubscribe');
        });
    });

    // Şikayet ve Moderasyon Rotaları (5651 & İhlal Bildirimi)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('reports', [ReportController::class, 'store']);

        // Engelleme (Blocking) Rotaları
        Route::prefix('users/{userId}/block')->controller(BlockController::class)->group(function () {
            Route::post('/', 'store');
            Route::delete('/', 'destroy');
        });
    });

    // Ödeme Webhook Rotaları (PayTR Asenkron Callback)
    Route::prefix('payment')->group(function () {
        Route::post('paytr/callback', [PaymentWebhookController::class, 'payTrCallback'])->name('payment.paytr.callback');
    });
});
