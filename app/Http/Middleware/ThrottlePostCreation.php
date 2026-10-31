<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottlePostCreation
{
    /**
     * Kullanıcı başına 1 dakikada izin verilen maksimum gönderi sayısı.
     */
    protected const MAX_POSTS_PER_MINUTE = 3;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $key = 'throttle:post:' . ($user ? (string) $user->id : $request->ip());

        if (RateLimiter::tooManyAttempts($key, self::MAX_POSTS_PER_MINUTE)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'success' => false,
                'error' => 'Post Rate Limit Exceeded',
                'message' => 'Çok hızlı paylaşım yapıyorsunuz. Spamı önlemek amacıyla dakikada en fazla 3 gönderi paylaşabilirsiniz.',
                'retry_after' => $seconds,
            ], Response::HTTP_TOO_MANY_REQUESTS, [
                'Retry-After' => (string) $seconds,
                'X-RateLimit-Limit' => (string) self::MAX_POSTS_PER_MINUTE,
                'X-RateLimit-Remaining' => '0',
            ]);
        }

        RateLimiter::hit($key, 60);

        /** @var Response $response */
        $response = $next($request);

        $remaining = RateLimiter::remaining($key, self::MAX_POSTS_PER_MINUTE);
        $response->headers->set('X-RateLimit-Limit', (string) self::MAX_POSTS_PER_MINUTE);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);

        return $response;
    }
}

