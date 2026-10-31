<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PostScope;
use App\Models\Location;
use App\Models\Post;
use App\Models\User;
use App\Services\FeedService;
use Illuminate\Console\Command;

class SimulateLocalTrendsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'simulate:local-trends {--time=auto : morning, noon, evening veya auto}';

    /**
     * @var string
     */
    protected $description = 'Pilot ilçelerde etkileşim yaratacak yerel soru ve tartışma konularını simüle ederek yayınlar.';

    public function handle(FeedService $feedService): int
    {
        $this->info('💬 Yerel etkileşim ve trend simülatörü çalışıyor...');

        $botUser = User::query()->where('username', 'mahalle_rehberi')->first();
        if (! $botUser) {
            $this->error('❌ "mahalle_rehberi" bot kullanıcısı bulunamadı.');
            return self::FAILURE;
        }

        // Günün saatine göre konu havuzu belirle
        $timeOption = $this->option('time');
        $hour = (int) now()->format('H');

        $period = match ($timeOption) {
            'morning' => 'morning',
            'noon' => 'noon',
            'evening' => 'evening',
            default => ($hour < 11 ? 'morning' : ($hour < 16 ? 'noon' : 'evening')),
        };

        $promptsByDistrict = [
            'Keçiören' => [
                'morning' => [
                    "Günaydın Keçiören! ☀️ Sabah simidi ve taze çay için mahallenizde en sevdiğiniz fırın veya çay ocağı neresi?",
                    "Keçiören'de güne güzel bir sabah yürüyüşüyle başlamak isteyenlere hangi parkı önerirsiniz? Kartaltepe mi, Ihlamur Vadisi mi?",
                ],
                'noon' => [
                    "Keçiören'de öğle yemeğinde en lezzetli döner ve pide nerede yenir? Mahalle sakinleri favorilerini yoruma bıraksın! 🥙",
                    "İlçemizde son zamanlarda açılan ve mutlaka denenmeli dediğiniz yeni bir kafe veya restoran var mı?",
                ],
                'evening' => [
                    "Keçiören'de bu hafta sonu ailecek veya arkadaşlarla nereye gidilir? Teleferik turu mu, Estergon Kalesi mi? Önerileri alalım! 🏰",
                    "İlçemizin en sakin, kafa dinlemelik köşesi sizce neresi?",
                ],
            ],
            'Sandıklı' => [
                'morning' => [
                    "Hayırlı sabahlar Sandıklı! ☀️ Güne meşhur Sandıklı haşhaşlı pidesiyle başlayanlar burada mı?",
                ],
                'noon' => [
                    "Sandıklı'da en iyi güveç ve leblebi nerede alınır? Dışarıdan gelen misafire ilk tavsiyeniz ne olurdu? 🍲",
                ],
                'evening' => [
                    "Sandıklı Hüdai Kaplıcaları'nda akşam yürüyüşü bir başka güzel. Bu hafta sonu termal planı yapan var mı?",
                ],
            ],
            'Kadıköy' => [
                'morning' => [
                    "Günaydın Kadıköy! ☕ Moda sahilinde sabah kahvesi için favori noktanız neresi?",
                ],
                'noon' => [
                    "Kadıköy Çarşı'da öğle arasında en iyi esnaf lokantası sizce hangisi?",
                ],
                'evening' => [
                    "Bu akşam Kadıköy sokaklarında canlı müzik veya sakin bir kitap kafe arayanlara öneriniz nedir? 📚🎵",
                ],
            ],
        ];

        $publishedCount = 0;

        foreach ($promptsByDistrict as $districtName => $periods) {
            $location = Location::query()->where('district', $districtName)->first();
            if (! $location) {
                continue;
            }

            $questions = $periods[$period] ?? $periods['noon'];
            $selectedQuestion = $questions[array_rand($questions)];

            // Bugün aynı ilçe için benzer bir soru sorulmuş mu kontrol et
            $alreadyPostedToday = Post::query()
                ->withoutGlobalScopes()
                ->where('user_id', $botUser->id)
                ->where('location_id', $location->id)
                ->where('created_at', '>=', now()->startOfDay())
                ->where('content', $selectedQuestion)
                ->exists();

            if (! $alreadyPostedToday) {
                Post::query()->create([
                    'user_id' => $botUser->id,
                    'content' => $selectedQuestion,
                    'scope' => PostScope::LOCAL,
                    'location_id' => $location->id,
                    'is_hidden' => false,
                ]);

                $feedService->clearLocalFeedCache($location->id);
                $publishedCount++;
                $this->line("  ✓ [{$districtName} - {$period}] Trend konusu yayınlandı.");
            }
        }

        $this->info("✨ Toplam {$publishedCount} adet yerel tartışma konusu akışlara bırakıldı.");

        return self::SUCCESS;
    }
}

