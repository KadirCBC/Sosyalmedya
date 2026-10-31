<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\SmsServiceInterface;
use App\Enums\AdStatus;
use App\Models\Ad;
use App\Services\Push\WebPushService;
use Illuminate\Console\Command;

class CheckExpiredAdsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ads:check-expired';

    /**
     * @var string
     */
    protected $description = 'Süresi biten reklamları sonlandırır ve esnafa gösterim/tıklanma özet SMS raporu gönderir.';

    public function handle(SmsServiceInterface $smsService, WebPushService $webPushService): int
    {
        $this->info('📊 Süresi dolan reklam kampanyaları denetleniyor...');

        $expiredAds = Ad::query()
            ->with(['user', 'location'])
            ->where('status', AdStatus::ACTIVE)
            ->where('ends_at', '<=', now())
            ->whereNull('reported_at')
            ->get();

        $processedCount = 0;

        foreach ($expiredAds as $ad) {
            $ad->update([
                'status' => AdStatus::EXPIRED,
                'reported_at' => now(),
            ]);

            $impressions = number_format($ad->impressions_count);
            $clicks = number_format($ad->clicks_count);

            // 1. Esnafa Performans Özet SMS'i Gönder
            if ($ad->user && $ad->user->phone) {
                $smsText = "Meydan: '{$ad->title}' mahalle reklamınız tamamlandı. Toplam {$impressions} kişiye ulaştı, {$clicks} kişi tıkladı. Yeni kampanyalarda görüşmek üzere!";
                $smsService->send($ad->user->phone, $smsText);
            }

            // 2. Kilit Ekranı Bildirimi Gönder
            if ($ad->user) {
                $webPushService->notifyUser(
                    user: $ad->user,
                    title: 'Reklam Kampanyanız Tamamlandı! 📊',
                    body: "İlanınız {$impressions} mahalle sakinine ulaştı ve {$clicks} kişi tıkladı.",
                    url: url("/ads/create")
                );
            }

            $this->line("  ✓ İlan ID #{$ad->id} ({$ad->title}) sonlandırıldı ve rapor SMS'i iletildi.");
            $processedCount++;
        }

        $this->info("✅ Toplam {$processedCount} adet reklam kampanyası sonuçlandırıldı.");

        return self::SUCCESS;
    }
}

