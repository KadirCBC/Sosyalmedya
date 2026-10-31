<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottleInteractions
{
    /**
     * Kullanıcı başına 1 dakikada izin verilen maksimum beğeni/yorum sayısı.
     */
    protected const MAX_INTERACTIONS_PER_MINUTE = 20;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $key = 'throttle:interactions:' . ($user ? (string) $user->id : $request->ip());

        if (RateLimiter::tooManyAttempts($key, self::MAX_INTERACTIONS_PER_MINUTE)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'error' => 'Interaction Rate Limit Exceeded',
                'message' => 'Çok hızlı işlem yapıyorsunuz. Bot ve flood koruması gereği dakikada en fazla 20 beğeni veya yorum yapabilirsiniz.',
                'retry_after' => $seconds,
            ], Response::HTTP_TOO_MANY_REQUESTS, [
                'Retry-After' => (string) $seconds,
                'X-RateLimit-Limit' => (string) self::MAX_INTERACTIONS_PER_MINUTE,
                'X-RateLimit-Remaining' => '0',
            ]);
        }

        RateLimiter::hit($key, 60);

        /** @var Response $response */
        $response = $next($request);

        $remaining = RateLimiter::remaining($key, self::MAX_INTERACTIONS_PER_MINUTE);
        $response->headers->set('X-RateLimit-Limit', (string) self::MAX_INTERACTIONS_PER_MINUTE);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);

        return $response;
    }
}

