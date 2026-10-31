<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
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
        // 1. Gönderi Paylaşma Limiti: Kullanıcı başına dakikada maksimum 3 gönderi
        RateLimiter::for('posts.store', function (Request $request) {
            $user = $request->user();
            $key = $user ? (string) $user->id : $request->ip();

            return Limit::perMinute(3)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Post Rate Limit Exceeded',
                        'message' => 'Dakikada en fazla 3 gönderi paylaşabilirsiniz. Lütfen bekleyin.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });

        // 2. Beğeni ve Yorum Limiti: Kullanıcı başına dakikada maksimum 20 işlem
        RateLimiter::for('interactions', function (Request $request) {
            $user = $request->user();
            $key = $user ? (string) $user->id : $request->ip();

            return Limit::perMinute(20)
                ->by($key)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'error' => 'Interaction Rate Limit Exceeded',
                        'message' => 'Dakikada en fazla 20 beğeni veya yorum yapabilirsiniz. Lütfen bekleyin.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });

        // 3. Genel API Flood Koruması: IP başına dakikada 120 istek
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });
    }
}

