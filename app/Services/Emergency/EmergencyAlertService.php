<?php

declare(strict_types=1);

namespace App\Services\Emergency;

use App\Jobs\BroadcastEmergencyAlertJob;
use App\Models\EmergencyAlert;
use App\Models\User;
use App\Services\Moderation\ContentFilterService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EmergencyAlertService
{
    public function __construct(
        protected ContentFilterService $contentFilterService
    ) {}

    /**
     * Yeni acil durum uyarısı oluşturur.
     * Yönetici ve moderatörler tarafından açılanlar otomatik doğrulanır ve anında yayına girer.
     * Normal vatandaşların açtığı acil uyarılar moderasyon onayına düşer (Asılsız ihbar ve trollük koruması).
     *
     * @param array<string, mixed> $data
     */
    public function createAlert(User $user, array $data): EmergencyAlert
    {
        $locationId = (int) ($data['location_id'] ?? $user->location_id ?? 1);

        $cleanTitle = $this->contentFilterService->inspectAndSanitize((string) $data['title']);
        $cleanDesc = $this->contentFilterService->inspectAndSanitize((string) $data['description']);

        $isPrivileged = $user->isSuperAdmin() || $user->isLocalModerator();
        $isVerified = $isPrivileged;

        /** @var EmergencyAlert $alert */
        $alert = EmergencyAlert::query()->create([
            'user_id' => $user->id,
            'location_id' => $locationId,
            'alert_type' => (string) $data['alert_type'],
            'title' => $cleanTitle,
            'description' => $cleanDesc,
            'contact_phone' => ! empty($data['contact_phone']) ? (string) $data['contact_phone'] : null,
            'latitude' => ! empty($data['latitude']) ? (float) $data['latitude'] : null,
            'longitude' => ! empty($data['longitude']) ? (float) $data['longitude'] : null,
            'is_verified' => $isVerified,
            'status' => 'active',
            'verified_at' => $isVerified ? now() : null,
            'verified_by' => $isVerified ? $user->id : null,
        ]);

        if ($isVerified) {
            $this->clearLocationCache($locationId);
            BroadcastEmergencyAlertJob::dispatch($alert);
        }

        return $alert->load(['user', 'location']);
    }

    /**
     * Moderatör veya yönetici tarafından acil durum ilanını doğrular ve anında ilçe genelinde yayınlar.
     */
    public function verifyAlert(User $moderator, int $alertId): EmergencyAlert
    {
        /** @var EmergencyAlert $alert */
        $alert = EmergencyAlert::query()->findOrFail($alertId);

        if (! $moderator->isSuperAdmin() && ! $moderator->canModerateLocation($alert->location_id)) {
            throw new AccessDeniedHttpException('Bu bölge için acil durum doğrulama yetkiniz bulunmamaktadır.');
        }

        $alert->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $moderator->id,
            'status' => 'active',
        ]);

        $this->clearLocationCache($alert->location_id);

        // Gecikmesiz ilçe geneli WebPush ve SMS fırlat
        BroadcastEmergencyAlertJob::dispatch($alert);

        return $alert->load(['user', 'location', 'verifier']);
    }

    /**
     * Acil durumu 'Çözüldü' olarak kapatır (Kayıp bulundu, kan temin edildi, afet müdahalesi tamamlandı).
     */
    public function resolveAlert(User $user, int $alertId): EmergencyAlert
    {
        /** @var EmergencyAlert $alert */
        $alert = EmergencyAlert::query()->findOrFail($alertId);

        // İlanı açan kişi veya yetkili moderatör kapatabilir
        if ($alert->user_id !== $user->id && ! $user->isSuperAdmin() && ! $user->canModerateLocation($alert->location_id)) {
            throw new AccessDeniedHttpException('Bu ilanı kapatma yetkiniz bulunmamaktadır.');
        }

        $alert->update(['status' => 'resolved']);
        $this->clearLocationCache($alert->location_id);

        return $alert;
    }

    /**
     * Belirli bir ilçedeki aktif ve doğrulanmış acil durumları getirir (Kırmızı Akış).
     * 30 saniyelik mikro önbellek ile sunucu yükünü sıfıra indirir.
     *
     * @return Collection<int, EmergencyAlert>
     */
    public function getActiveAlertsForLocation(int $locationId): Collection
    {
        $cacheKey = "emergency:active:{$locationId}";

        return Cache::remember($cacheKey, 30, function () use ($locationId) {
            return EmergencyAlert::query()
                ->where('location_id', $locationId)
                ->where('status', 'active')
                ->where('is_verified', true)
                ->orderByDesc('id')
                ->with(['location:id,city,district', 'verifier:id,name'])
                ->get();
        });
    }

    /**
     * İlçenin aktif acil durum önbelleğini temizler.
     */
    public function clearLocationCache(int $locationId): void
    {
        Cache::forget("emergency:active:{$locationId}");
    }
}

