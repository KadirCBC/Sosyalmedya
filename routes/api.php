<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BlockController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\FeedController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Sosyal Medya Platformu (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

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

    // Gönderi (Post) ve Etkileşim Rotaları
    Route::prefix('posts')->controller(PostController::class)->group(function () {
        Route::get('{id}', 'show');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', 'store');
            Route::post('{id}/like', 'toggleLike');
        });
    });

    // Yorum (Comment) Rotaları
    Route::prefix('posts/{postId}/comments')->controller(CommentController::class)->group(function () {
        Route::get('/', 'index');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', 'store');
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

    // Şikayet ve Moderasyon Rotaları (5651 & İhlal Bildirimi)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('reports', [ReportController::class, 'store']);

        // Engelleme (Blocking) Rotaları
        Route::prefix('users/{userId}/block')->controller(BlockController::class)->group(function () {
            Route::post('/', 'store');
            Route::delete('/', 'destroy');
        });
    });
});
