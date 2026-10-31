<?php

declare(strict_types=1);

namespace App\Services\Ad;

use App\Contracts\SmsServiceInterface;
use App\Enums\AdStatus;
use App\Models\Ad;
use App\Models\Location;
use App\Models\User;
use App\Services\FeedService;
use App\Services\MediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdWizardService
{
    public function __construct(
        protected MediaService $mediaService,
        protected FeedService $feedService,
        protected SmsServiceInterface $smsService
    ) {}

    /**
     * Esnaf için taslak reklam kaydını oluşturur.
     *
     * @param array<string, mixed> $data
     */
    public function createPendingAd(User $user, array $data, ?UploadedFile $image = null): Ad
    {
        $durationDays = max(1, (int) ($data['duration_days'] ?? 3));
        $dailyBudget = max(50.00, (float) ($data['daily_budget'] ?? 100.00));
        $totalBudget = $durationDays * $dailyBudget;

        // Hedef ilçe (seçilmediyse esnafın kayıtlı konumu)
        $locationId = ! empty($data['location_id'])
            ? (int) $data['location_id']
            : (int) ($user->location_id ?? 1);

        // Varsa reklam görselini R2'ye yükle
        $imageUrl = null;
        if ($image) {
            $relativePath = $this->mediaService->uploadImageAsWebp($image, 'ads');
            $imageUrl = $this->mediaService->getUrl($relativePath);
        }

        // İletişim Butonu (CTA) URL'ini biçimlendir
        $ctaType = (string) ($data['cta_type'] ?? 'whatsapp');
        $ctaTarget = (string) ($data['cta_target'] ?? '');
        $ctaUrl = $this->formatCtaUrl($ctaType, $ctaTarget, $data['title'] ?? '');
        $ctaText = $this->getCtaLabel($ctaType);

        $merchantOid = 'AD-' . strtoupper(Str::random(8)) . '-' . time();

        return Ad::query()->create([
            'merchant_oid' => $merchantOid,
            'user_id' => $user->id,
            'location_id' => $locationId,
            'title' => (string) $data['title'],
            'content' => (string) $data['content'],
            'image_url' => $imageUrl,
            'cta_text' => $ctaText,
            'cta_url' => $ctaUrl,
            'cta_type' => $ctaType,
            'status' => AdStatus::PENDING,
            'budget' => $totalBudget,
            'daily_budget' => $dailyBudget,
            'duration_days' => $durationDays,
            'payment_method' => 'paytr',
        ]);
    }

    /**
     * Ödeme tamamlandığında reklamı anında aktifleştirir ve esnafa SMS atar.
     */
    public function activateAdAfterPayment(string $merchantOid, float $paidAmount): ?Ad
    {
        /** @var Ad|null $ad */
        $ad = Ad::query()->with(['user', 'location'])->where('merchant_oid', $merchantOid)->first();

        if (! $ad || $ad->status === AdStatus::ACTIVE) {
            return $ad;
        }

        DB::transaction(function () use ($ad, $paidAmount) {
            $ad->update([
                'status' => AdStatus::ACTIVE,
                'paid_amount' => $paidAmount,
                'starts_at' => now(),
                'ends_at' => now()->addDays($ad->duration_days),
            ]);

            // Yerel akış önbelleğini temizle (reklam anında akışta görünsün)
            if ($ad->location_id !== null) {
                $this->feedService->clearLocalFeedCache($ad->location_id);
            }
        });

        // Esnafa onay SMS'i gönder
        if ($ad->user && $ad->user->phone) {
            $districtName = $ad->location->district ?? 'Bölgenizde';
            $smsMessage = "Meydan: Tebrikler! '{$ad->title}' reklamınız onaylandı ve {$districtName} akışında yayına alındı. Bol kazançlar!";
            $this->smsService->send($ad->user->phone, $smsMessage);
        }

        return $ad;
    }

    /**
     * Günlük bütçeye göre tahmini mahalle gösterim sayısını hesaplar.
     */
    public static function calculateEstimatedReach(float $dailyBudget, int $days): array
    {
        // 100 TL ortalama 2.500 - 4.000 yerel gösterim üretir
        $minPerDay = (int) round($dailyBudget * 25);
        $maxPerDay = (int) round($dailyBudget * 40);

        return [
            'min' => $minPerDay * $days,
            'max' => $maxPerDay * $days,
        ];
    }

    /**
     * CTA tipine göre hedef linki biçimlendirir.
     */
    protected function formatCtaUrl(string $type, string $target, string $adTitle): string
    {
        $target = trim($target);

        return match ($type) {
            'whatsapp' => 'https://wa.me/' . preg_replace('/\D+/', '', $target) . '?text=' . urlencode("Merhaba, Meydan'daki \"{$adTitle}\" ilanınız için yazıyorum."),
            'phone' => 'tel:' . preg_replace('/\D+/', '', $target),
            'map' => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($target),
            default => str_starts_with($target, 'http') ? $target : 'https://' . $target,
        };
    }

    /**
     * Buton metni şablonları.
     */
    protected function getCtaLabel(string $type): string
    {
        return match ($type) {
            'whatsapp' => "WhatsApp'tan Yaz",
            'phone' => 'Hemen Ara',
            'map' => 'Yol Tarifi Al',
            default => 'Web Sitesine Git',
        };
    }
}

