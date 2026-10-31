<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NotificationController extends Controller
{
    /**
     * Kullanıcının bildirimlerini listeler (cursor pagination ile).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $notifications = $request->user()
            ->notifications()
            ->cursorPaginate(20);

        return NotificationResource::collection($notifications);
    }

    /**
     * Belirli bir bildirimi okundu olarak işaretler.
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'message' => 'Bildirim okundu olarak işaretlendi.',
        ], Response::HTTP_OK);
    }

    /**
     * Kullanıcının tüm okunmamış bildirimlerini okundu olarak işaretler.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'Tüm bildirimler okundu olarak işaretlendi.',
        ], Response::HTTP_OK);
    }

    /**
     * Server-Sent Events (SSE) tabanlı gerçek zamanlı bildirim akışı.
     * cPanel / Plesk ortamlarında Node.js veya harici servis gerektirmeden
     * HTTP üzerinden anlık bildirim iletimi sağlar.
     */
    public function stream(Request $request): StreamedResponse
    {
        $user = $request->user();
        $lastEventTime = now()->subSeconds(10);
        $startTime = time();
        $maxDurationSeconds = 25; // 25 saniye sonra bağlantı yenilenir (PHP-FPM havuzunu tıkamaz)

        // Session kilitlemesini önlemek için session'ı serbest bırak
        if (session()->isStarted()) {
            session()->save();
        }

        return response()->stream(function () use ($user, &$lastEventTime, $startTime, $maxDurationSeconds) {
            // Çıktı tamponlamasını temizle
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            while (time() - $startTime < $maxDurationSeconds) {
                // İstemci sekmesini kapattıysa döngüyü sonlandır
                if (connection_aborted()) {
                    break;
                }

                // Son kontrolden sonra gelen yeni bildirimleri çek
                $newNotifications = $user->notifications()
                    ->where('created_at', '>', $lastEventTime)
                    ->orderBy('created_at', 'asc')
                    ->get();

                if ($newNotifications->isNotEmpty()) {
                    foreach ($newNotifications as $notification) {
                        $lastEventTime = $notification->created_at;
                        $payload = json_encode(new NotificationResource($notification));

                        echo "id: {$notification->id}\n";
                        echo "event: notification\n";
                        echo "data: {$payload}\n\n";
                    }

                    flush();
                }

                // Bağlantının kopmasını önlemek için 3 saniyede bir ping/heartbeat gönder
                echo ": ping\n\n";
                flush();

                sleep(3);
            }
        }, Response::HTTP_OK, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Nginx/Plesk reverse proxy tamponlamasını kapatır
        ]);
    }
}

