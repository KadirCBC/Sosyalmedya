<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IpSpamDefenseMiddleware
{
    /**
     * IP başına 1 dakikada izin verilen maksimum genel istek sayısı (Flood eşiği).
     */
    protected const FLOOD_BURST_LIMIT = 150;

    /**
     * Flood eşiğini aşan IP'nin geçici engellenme süresi (saniye - 15 dakika).
     */
    protected const BAN_DURATION_SECONDS = 900;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();

        if (! $ip) {
            return $next($request);
        }

        $banKey = "defense:banned_ip:{$ip}";

        // 1. IP Geçici Olarak Engellenmiş mi Kontrolü
        if (Cache::has($banKey)) {
            $remainingSeconds = (int) Cache::get("defense:ban_expires:{$ip}", self::BAN_DURATION_SECONDS);

            Log::warning("Engellenen IP'den istek reddedildi", [
                'ip' => $ip,
                'path' => $request->path(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Too Many Requests',
                'message' => 'Şüpheli flood/spam aktivitesi tespit edildi. IP adresiniz geçici olarak engellenmiştir.',
                'retry_after' => $remainingSeconds,
            ], Response::HTTP_TOO_MANY_REQUESTS, [
                'Retry-After' => (string) $remainingSeconds,
                'X-RateLimit-Reset' => (string) (time() + $remainingSeconds),
            ]);
        }

        // 2. IP Bazlı Dakikalık İstek Sayacı
        $minuteKey = "defense:req_count:{$ip}:" . floor(time() / 60);
        $requestCount = (int) Cache::get($minuteKey, 0) + 1;
        Cache::put($minuteKey, $requestCount, 65);

        // Flood Eşiği Aşıldı mı?
        if ($requestCount > self::FLOOD_BURST_LIMIT) {
            // IP'yi 15 dakika banla
            Cache::put($banKey, true, self::BAN_DURATION_SECONDS);
            Cache::put("defense:ban_expires:{$ip}", self::BAN_DURATION_SECONDS, self::BAN_DURATION_SECONDS);

            Log::alert("Aşırı istek (Flood) nedeniyle IP otomatik engellendi (15 Dk)", [
                'ip' => $ip,
                'request_count' => $requestCount,
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Rate Limit Flood Block',
                'message' => 'Çok fazla istek gönderdiniz. Güvenlik önlemi olarak IP adresiniz 15 dakika süreyle engellendi.',
                'retry_after' => self::BAN_DURATION_SECONDS,
            ], Response::HTTP_TOO_MANY_REQUESTS, [
                'Retry-After' => (string) self::BAN_DURATION_SECONDS,
            ]);
        }

        /** @var Response $response */
        $response = $next($request);

        $remaining = max(0, self::FLOOD_BURST_LIMIT - $requestCount);
        $response->headers->set('X-RateLimit-Limit', (string) self::FLOOD_BURST_LIMIT);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);

        return $response;
    }
}

