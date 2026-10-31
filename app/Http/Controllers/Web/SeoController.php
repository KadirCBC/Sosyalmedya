<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Enums\PostScope;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Location;
use App\Models\Post;
use App\Services\Seo\SeoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SeoController extends Controller
{
    public function __construct(
        protected SeoService $seoService
    ) {}

    /**
     * Programmatic SEO: Dinamik İlçe Açılış Sayfası (/yerel/{citySlug}/{districtSlug})
     */
    public function localLandingPage(Request $request, string $citySlug, string $districtSlug): View
    {
        /** @var Location|null $location */
        $location = $this->seoService->findLocationBySlugs($citySlug, $districtSlug);

        if (! $location) {
            abort(404, 'Belirtilen il veya ilçe bulunamadı.');
        }

        // Son 20 yerel gönderiyi idx_local_feed indeksini kullanarak getir
        $posts = Post::query()
            ->where('scope', PostScope::LOCAL)
            ->where('location_id', $location->id)
            ->orderByDesc('id')
            ->with(['user:id,name,username,avatar_path', 'location:id,city,district'])
            ->take(20)
            ->get();

        $postsData = $posts->map(fn ($p) => (new PostResource($p))->resolve());

        // Yerel anlık hava durumu (1 saat önbellekli)
        $weather = $this->seoService->getLocalWeather($location);

        // Kanonik URL ve Başlıklar
        $canonicalUrl = url("/yerel/{$citySlug}/{$districtSlug}");
        $pageTitle = "{$location->district} ({$location->city}) Canlı Mahalle Akışı ve Gündemi | Meydan";
        $pageDescription = "{$location->city} ili {$location->district} ilçesine ait son dakika yerel paylaşımları, belediye duyuruları, esnaf fırsatları ve mahalle sohbetleri.";

        // Schema.org JSON-LD Yapılandırılmış Verisi
        $jsonLd = $this->seoService->generateDistrictSchema($location, $posts, $canonicalUrl);

        return view('seo.district', [
            'location' => $location,
            'posts' => $postsData,
            'weather' => $weather,
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'canonicalUrl' => $canonicalUrl,
            'citySlug' => $citySlug,
            'districtSlug' => $districtSlug,
            'jsonLd' => $jsonLd,
        ]);
    }

    /**
     * Dinamik Sosyal Kart Üretici (OG Image Generator - 1200x630 PNG).
     * WhatsApp, X (Twitter), Facebook ve Telegram paylaşımları için anlık grafik oluşturur.
     */
    public function ogImage(int $id): Response
    {
        /** @var Post $post */
        $post = Post::query()->with(['user', 'location'])->findOrFail($id);

        $width = 1200;
        $height = 630;

        // Görsel tuvali oluştur (GD Kütüphanesi - cPanel/Plesk uyumlu)
        $image = imagecreatetruecolor($width, $height);

        // Renk tanımları
        $bgDark = imagecolorallocate($image, 15, 23, 42); // #0f172a (Koyu Arka Plan)
        $cardBg = imagecolorallocate($image, 30, 41, 59); // #1e293b (İç Kart)
        $borderCol = imagecolorallocate($image, 51, 65, 85); // #334155
        $textWhite = imagecolorallocate($image, 255, 255, 255);
        $textSlate = imagecolorallocate($image, 148, 163, 184); // #94a3b8
        $emeraldBg = imagecolorallocate($image, 6, 95, 70); // #065f46
        $emeraldText = imagecolorallocate($image, 52, 211, 153); // #34d399
        $blueCol = imagecolorallocate($image, 59, 130, 246); // #3b82f6

        // 1. Arka planı doldur
        imagefilledrectangle($image, 0, 0, $width, $height, $bgDark);

        // 2. İç kart kutusu çiz
        imagefilledrectangle($image, 60, 60, $width - 60, $height - 60, $cardBg);
        imagerectangle($image, 60, 60, $width - 60, $height - 60, $borderCol);

        // 3. Üst Bar: Meydan Marka Logosu & Konum Rozeti
        imagestring($image, 5, 100, 100, "M E Y D A N", $blueCol);

        $locationName = $post->location
            ? "Konum: " . $post->location->city . " / " . $post->location->district
            : "Genel Akis";
        
        // Konum Rozeti Kutusu
        imagefilledrectangle($image, 780, 95, 1100, 135, $emeraldBg);
        imagestring($image, 4, 800, 105, $locationName, $emeraldText);

        // 4. Yazar Bilgisi
        $authorName = (string) ($post->user->name ?? 'Meydan Sakini');
        $username = "@" . ($post->user->username ?? 'kullanici');
        imagestring($image, 5, 100, 170, $authorName, $textWhite);
        imagestring($image, 4, 100, 200, $username, $textSlate);

        // 5. Gönderi İçeriği (Satırlara Bölerek Yaz)
        $cleanContent = preg_replace('/\s+/', ' ', trim($post->content)) ?? '';
        $wrappedLines = explode("\n", wordwrap($cleanContent, 65, "\n"));
        $y = 260;
        foreach (array_slice($wrappedLines, 0, 5) as $line) {
            imagestring($image, 5, 100, $y, $line, $textWhite);
            $y += 32;
        }

        // 6. Alt Bilgi: Beğeni / Yorum & Platform Daveti
        $statsText = "Begeniler: {$post->likes_count}   |   Yorumlar: {$post->comments_count}";
        imagestring($image, 4, 100, 520, $statsText, $textSlate);
        imagestring($image, 4, 760, 520, "meydan.com'da kesfet ->", $blueCol);

        // Çıktıyı yakala
        ob_start();
        imagepng($image);
        $pngData = (string) ob_get_clean();
        imagedestroy($image);

        return response($pngData, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=604800, immutable', // 7 gün önbellek
        ]);
    }

    /**
     * Otomatik Dinamik Sitemap (sitemap.xml).
     * 81 ilin tüm 973 ilçesini, popüler gönderileri ve ana sayfayı içerir.
     */
    public function sitemap(): Response
    {
        $xml = Cache::remember('seo:sitemap:xml', 86400, function () {
            $districtSlugs = $this->seoService->getAllDistrictSlugs();

            // Son 100 genel gönderi
            $recentPosts = Post::query()
                ->where('scope', PostScope::GENERAL)
                ->where('is_hidden', false)
                ->orderByDesc('id')
                ->take(100)
                ->get(['id', 'updated_at']);

            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

            // 1. Ana Sayfa ve Akış
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . url('/') . "</loc>\n";
            $xml .= "    <changefreq>always</changefreq>\n";
            $xml .= "    <priority>1.0</priority>\n";
            $xml .= "  </url>\n";

            // 2. 973 İlçe Açılış Sayfası (Programmatic SEO)
            foreach ($districtSlugs as $d) {
                $locUrl = url("/yerel/{$d['city_slug']}/{$d['district_slug']}");
                $xml .= "  <url>\n";
                $xml .= "    <loc>{$locUrl}</loc>\n";
                $xml .= "    <lastmod>{$d['updated_at']}</lastmod>\n";
                $xml .= "    <changefreq>hourly</changefreq>\n";
                $xml .= "    <priority>0.9</priority>\n";
                $xml .= "  </url>\n";
            }

            // 3. Son Popüler Gönderiler
            foreach ($recentPosts as $post) {
                $postUrl = url("/posts/{$post->id}");
                $lastMod = $post->updated_at?->toAtomString() ?? now()->toAtomString();
                $xml .= "  <url>\n";
                $xml .= "    <loc>{$postUrl}</loc>\n";
                $xml .= "    <lastmod>{$lastMod}</lastmod>\n";
                $xml .= "    <changefreq>weekly</changefreq>\n";
                $xml .= "    <priority>0.7</priority>\n";
                $xml .= "  </url>\n";
            }

            $xml .= '</urlset>';

            return $xml;
        });

        return response($xml, 200, [
            'Content-Type' => 'text/xml; charset=utf-8',
        ]);
    }
}

