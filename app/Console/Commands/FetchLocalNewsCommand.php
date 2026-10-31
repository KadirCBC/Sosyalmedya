<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\PostScope;
use App\Models\Location;
use App\Models\Post;
use App\Models\User;
use App\Services\FeedService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FetchLocalNewsCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'seed:local-news {--pilot=all : Keçiören, Sandıklı veya all}';

    /**
     * @var string
     */
    protected $description = 'Pilot ilçeler için resmi belediye duyuruları, kesinti bültenleri ve yerel haberleri çeker ve yayınlar.';

    public function handle(FeedService $feedService): int
    {
        $this->info('🛰️ Yerel haber ve resmi duyuru tohumlayıcı başlatılıyor...');

        // Resmi Duyuru Botu kullanıcısını bul
        $botUser = User::query()->where('username', 'resmi_duyuru')->first();
        if (! $botUser) {
            $this->error('❌ "resmi_duyuru" kullanıcı hesabı bulunamadı. Lütfen önce "php artisan db:seed" çalıştırın.');
            return self::FAILURE;
        }

        // Pilot ilçeleri tanımla
        $pilotDistricts = [
            [
                'city' => 'Ankara',
                'district' => 'Keçiören',
                'rss_urls' => [
                    'https://www.kecioren.bel.tr/rss.aspx',
                ],
                'bulletins' => [
                    [
                        'tag' => '💧 ASKİ Su Kesintisi Bülteni',
                        'content' => "Keçiören Ayvalı ve Esertepe mahallelerinde ana isale hattı yenileme çalışması nedeniyle bugün 13:00 - 17:00 saatleri arasında planlı su kesintisi uygulanacaktır. Mahalle sakinlerimizin tedbirli olması rica olunur.",
                    ],
                    [
                        'tag' => '📢 Belediye Kültür Etkinliği',
                        'content' => "Keçiören Neşet Ertaş Sanat ve Gösteri Merkezi'nde bu Cuma akşamı saat 19:30'da halk müziği korosu konseri düzenlenecektir. Etkinliğimiz tüm mahalle sakinlerine ücretsiz ve halka açıktır.",
                    ],
                    [
                        'tag' => '⚽ Keçiörengücü Maç Sonucu',
                        'content' => "TFF 1. Lig temsilcimiz Ankara Keçiörengücü, Aktepe Stadı'nda oynanan mücadelede rakibini 2-1 mağlup ederek haftayı 3 puanla kapattı. Tebrikler Keçiörengücü!",
                    ],
                ],
            ],
            [
                'city' => 'Afyonkarahisar',
                'district' => 'Sandıklı',
                'rss_urls' => [
                    'https://www.sandikli.bel.tr/rss',
                ],
                'bulletins' => [
                    [
                        'tag' => '📢 Sandıklı Belediyesi Duyurusu',
                        'content' => "Sandıklı Hüdai Kaplıcaları tesislerimizde kış dönemi termal bakım ve yenileme çalışmaları tamamlanmıştır. İlçe sakinlerimize özel indirimli abonelik kayıtları belediye veznelerinden başlamıştır.",
                    ],
                    [
                        'tag' => '⚡ OEDAŞ Planlı Elektrik Kesintisi',
                        'content' => "Sandıklı Çakır ve Ece Mahallelerinde şebeke modernizasyonu nedeniyle yarın 09:00 - 12:00 saatleri arasında planlı elektrik kesintisi yaşanacaktır.",
                    ],
                    [
                        'tag' => '⚽ Sandıklıspor Bülteni',
                        'content' => "Sandıklıspor Bölgesel Amatör Lig hafta sonu karşılaşması hazırlıklarını ilçe stadyumunda tamamladı. Pazar günü saat 14:00'te tüm hemşehrilerimizi tribünlere bekliyoruz.",
                    ],
                ],
            ],
            [
                'city' => 'İstanbul',
                'district' => 'Kadıköy',
                'rss_urls' => [],
                'bulletins' => [
                    [
                        'tag' => '🎭 Kadıköy Tiyatro Festivali',
                        'content' => "Kadıköy Belediyesi Süreyya Operası ve Barış Manço Kültür Merkezi'nde bu hafta sonu 4 farklı bağımsız tiyatro oyunu sahneleniyor. Biletler kültür merkezlerimizden temin edilebilir.",
                    ],
                    [
                        'tag' => '🌊 İSKİ Altyapı Bülteni',
                        'content' => "Kadıköy Moda Caddesi yağmursuyu kanalı ayrıştırma projesi 2. etap çalışmaları başlamıştır. Trafik tek şeritten kontrollü olarak sağlanmaktadır.",
                    ],
                ],
            ],
        ];

        $totalSeeded = 0;

        foreach ($pilotDistricts as $pilot) {
            $location = Location::query()
                ->where('city', $pilot['city'])
                ->where('district', $pilot['district'])
                ->first();

            if (! $location) {
                $this->warn("⚠️ Konum bulunamadı: {$pilot['city']} - {$pilot['district']}");
                continue;
            }

            // A) Harici RSS Feed'leri Dene
            foreach ($pilot['rss_urls'] as $url) {
                try {
                    $response = Http::timeout(5)->get($url);
                    if ($response->successful()) {
                        $xml = @simplexml_load_string($response->body());
                        if ($xml && isset($xml->channel->item)) {
                            foreach (array_slice($xml->channel->item, 0, 2) as $item) {
                                $text = "📢 " . Str::limit(trim((string) $item->title) . "\n" . strip_tags(trim((string) $item->description)), 480);
                                if ($this->publishIfNotExists($botUser, $location, $text)) {
                                    $totalSeeded++;
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    // RSS ulaşılamazsa sessizce bülten fallback'ine geç
                }
            }

            // B) Resmi Bülten & Kesinti Şablonlarını Tohumla
            foreach ($pilot['bulletins'] as $bulletin) {
                $content = "{$bulletin['tag']}\n\n{$bulletin['content']}";

                if ($this->publishIfNotExists($botUser, $location, $content)) {
                    $totalSeeded++;
                    $this->line("  ✓ [{$location->district}] {$bulletin['tag']} paylaşıldı.");
                }
            }

            // İlçenin akış önbelleğini temizle
            $feedService->clearLocalFeedCache($location->id);
        }

        $this->info("✅ Toplam {$totalSeeded} adet yerel duyuru ve bülten başarıyla paylaşıldı!");

        return self::SUCCESS;
    }

    /**
     * Aynı metinle daha önce gönderi yapılmışsa mükerrer paylaşımı engeller.
     */
    protected function publishIfNotExists(User $user, Location $location, string $content): bool
    {
        $exists = Post::query()
            ->withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('location_id', $location->id)
            ->where('content', $content)
            ->exists();

        if ($exists) {
            return false;
        }

        Post::query()->create([
            'user_id' => $user->id,
            'content' => $content,
            'scope' => PostScope::LOCAL,
            'location_id' => $location->id,
            'is_hidden' => false,
        ]);

        return true;
    }
}

