<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\SmsServiceInterface;
use App\Models\EmergencyAlert;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\Push\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class BroadcastEmergencyAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Acil durum bildirimlerinin en yüksek öncelikle işlenmesi için timeout ve deneme sayısı.
     */
    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public EmergencyAlert $alert
    ) {
        $this->onQueue('emergency');
    }

    /**
     * İlgili ilçedeki tüm kullanıcılara gecikmesiz kilit ekranı WebPush ve SMS uyarısı gönderir.
     */
    public function handle(WebPushService $webPushService, SmsServiceInterface $smsService): void
    {
        $locationId = $this->alert->location_id;
        $locationName = $this->alert->location?->district ?? 'İlçeniz';
        $icon = $this->alert->icon;

        $pushTitle = "{$icon} ACİL DURUM: {$this->alert->title} ({$locationName})";
        $pushBody = mb_substr($this->alert->description, 0, 140);
        if ($this->alert->contact_phone) {
            $pushBody .= " | İletişim: {$this->alert->contact_phone}";
        }
        $targetUrl = "/emergency/{$this->alert->id}";

        Log::emergency("İlçe genelinde acil durum yayını başlatıldı", [
            'alert_id' => $this->alert->id,
            'alert_type' => $this->alert->alert_type,
            'location_id' => $locationId,
            'location_name' => $locationName,
        ]);

        // 1. İlgili ilçedeki tüm WebPush abonelerine toplu bildirim gönder
        $subscriptions = PushSubscription::query()
            ->whereHas('user', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            })
            ->with('user')
            ->get();

        $sentPushCount = 0;
        foreach ($subscriptions as $sub) {
            try {
                if ($sub->user) {
                    $webPushService->sendToUser(
                        $sub->user,
                        $pushTitle,
                        $pushBody,
                        $targetUrl
                    );
                    $sentPushCount++;
                }
            } catch (Throwable $e) {
                Log::warning("WebPush acil iletim hatası", [
                    'user_id' => $sub->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 2. Bölgedeki kayıtlı yetkili ve gönüllü muhtarlara acil durum SMS'i at
        $moderatorsAndMuhtars = User::query()
            ->where('location_id', $locationId)
            ->where(function ($q) {
                $q->where('role', 'local_moderator')
                    ->orWhere('role', 'super_admin')
                    ->orWhereHas('reputation', fn ($r) => $r->where('level', 'Gönüllü Muhtar'));
            })
            ->whereNotNull('phone')
            ->take(20)
            ->get();

        $smsMessage = "MEYDAN ACIL ({$locationName}): {$this->alert->title}. " . mb_substr($this->alert->description, 0, 80);
        if ($this->alert->contact_phone) {
            $smsMessage .= " Tel: {$this->alert->contact_phone}";
        }

        $sentSmsCount = 0;
        foreach ($moderatorsAndMuhtars as $mod) {
            try {
                $smsService->sendSms($mod->phone, $smsMessage);
                $sentSmsCount++;
            } catch (Throwable $e) {
                Log::warning("Acil SMS iletim hatası", [
                    'phone' => $mod->phone,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info("Acil durum yayını tamamlandı", [
            'alert_id' => $this->alert->id,
            'sent_push_count' => $sentPushCount,
            'sent_sms_count' => $sentSmsCount,
        ]);
    }
}

