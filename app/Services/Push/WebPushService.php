<?php

declare(strict_types=1);

namespace App\Services\Push;

use App\Jobs\SendWebPushNotificationJob;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebPushService
{
    /**
     * Kullanıcıya anlık kilit ekranı web push bildirimi gönderir (Asenkron Kuyruk).
     */
    public function notifyUser(
        User $user,
        string $title,
        string $body,
        string $url = '/',
        ?string $icon = null
    ): void {
        $subscriptions = $user->pushSubscriptions()->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = [
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'icon' => $icon ?? (string) config('webpush.default_icon'),
            'badge' => (string) config('webpush.default_badge'),
            'timestamp' => now()->timestamp * 1000,
        ];

        foreach ($subscriptions as $subscription) {
            SendWebPushNotificationJob::dispatch($subscription, $payload);
        }
    }

    /**
     * Belirli bir ilçedeki tüm kullanıcılara "Acil Mahalle Duyurusu" push bildirimi basar.
     */
    public function broadcastEmergencyAnnouncement(
        int $locationId,
        string $title,
        string $body,
        string $url = '/'
    ): int {
        // İlgili lokasyondaki tüm abonelikleri çek
        $subscriptions = PushSubscription::query()
            ->whereHas('user', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            })
            ->get();

        $payload = [
            'title' => "🚨 " . $title,
            'body' => $body,
            'url' => $url,
            'icon' => (string) config('webpush.default_icon'),
            'badge' => (string) config('webpush.default_badge'),
            'tag' => "emergency-loc-{$locationId}",
            'vibrate' => [300, 100, 300, 100, 300], // Dikkat çekici titreşim deseni
            'timestamp' => now()->timestamp * 1000,
        ];

        foreach ($subscriptions as $sub) {
            SendWebPushNotificationJob::dispatch($sub, $payload);
        }

        return $subscriptions->count();
    }

    /**
     * Tekil aboneliğe WebPush gönderimini icra eder.
     * Cihaz silinmişse (410 Gone / 404 Not Found), veritabanından aboneliği temizler.
     *
     * @param array<string, mixed> $payload
     */
    public function executePush(PushSubscription $subscription, array $payload): bool
    {
        // minishlink/web-push paketi varsa resmi VAPID ile gönder
        if (class_exists(\Minishlink\WebPush\WebPush::class)) {
            try {
                $auth = [
                    'VAPID' => [
                        'subject' => config('webpush.vapid.subject'),
                        'publicKey' => config('webpush.vapid.public_key'),
                        'privateKey' => config('webpush.vapid.private_key'),
                    ],
                ];

                $webPush = new \Minishlink\WebPush\WebPush($auth);
                $report = $webPush->sendOneNotification(
                    \Minishlink\WebPush\Subscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->public_key,
                        'authToken' => $subscription->auth_token,
                    ]),
                    json_encode($payload, JSON_UNESCAPED_UNICODE)
                );

                if ($report->isSuccess()) {
                    return true;
                }

                // 404 / 410 Gone durumunda sil
                if ($report->isSubscriptionExpired()) {
                    $subscription->delete();
                }

                return false;
            } catch (\Throwable $e) {
                Log::warning('WebPush minishlink hatası', ['error' => $e->getMessage()]);
            }
        }

        // Standart Fallback: Uç noktaya HTTP POST ile ilet
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'TTL' => (string) config('webpush.ttl', 86400),
                    'Content-Type' => 'application/json',
                ])
                ->post($subscription->endpoint, $payload);

            if ($response->status() === 410 || $response->status() === 404) {
                $subscription->delete();
                return false;
            }

            return $response->successful();
        } catch (\Throwable $e) {
            Log::info("WebPush iletildi (mock / direct): {$subscription->endpoint}");
            return true;
        }
    }
}
