<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\DistrictSentimentIndex;
use App\Models\DistrictTrend;
use App\Models\Location;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DistrictPulseService
{
    /**
     * Türkçe stop-words (etkisiz kelimeler) sözlüğü.
     * Analiz esnasında gürültüyü temizler.
     *
     * @var list<string>
     */
    protected array $stopWords = [
        've', 'veya', 'ile', 'ama', 'fakat', 'ancak', 'lakin', 'bu', 'şu', 'o',
        'bir', 'için', 'de', 'da', 'ki', 'mi', 'mu', 'mü', 'mı', 'ne', 'nasıl',
        'neden', 'niye', 'gibi', 'kadar', 'sonra', 'önce', 'daha', 'en', 'çok',
        'az', 'var', 'yok', 'ben', 'sen', 'biz', 'siz', 'onlar', 'bunu', 'şunu',
        'bana', 'sana', 'ona', 'bize', 'size', 'onlara', 'her', 'tüm', 'hep',
        'ise', 'diye', 'zaten', 'bile', 'çünkü', 'artık', 'yani', 'şimdi', 'böyle',
        'şöyle', 'kendi', 'olan', 'olarak', 'şey', 'şeyler', 'bazı', 'hiç', 'öyle',
        'göre', 'karşı', 'hem', 'ya', 'ise', 'biri', 'birisi', 'oldu', 'olur',
        'eden', 'etti', 'edecek', 'yapılan', 'yaptı', 'yapacak', 'gelen', 'gitti',
    ];

    /**
     * Pozitif duygu belirten anahtar kelimeler.
     *
     * @var list<string>
     */
    protected array $positiveWords = [
        'teşekkür', 'teşekkürler', 'harika', 'güzel', 'hızlı', 'başarılı', 'temiz',
        'beğendim', 'mutlu', 'iyiki', 'süper', 'muhteşem', 'tebrik', 'tebrikler',
        'çözüldü', 'aferin', 'huzurlu', 'kolay', 'memnun', 'kaliteli', 'sağol',
        'sevdim', 'kusursuz', 'sağolsun', 'ferah', 'düzenli', 'harikulade', 'kutlarım',
    ];

    /**
     * Negatif duygu ve belediye şikayet belirten anahtar kelimeler.
     *
     * @var list<string>
     */
    protected array $negativeWords = [
        'şikayet', 'kesinti', 'rezalet', 'bozuk', 'çöp', 'koku', 'pis', 'pislik',
        'geç', 'gelmedi', 'çukur', 'karanlık', 'tehlike', 'kaza', 'mağdur', 'mağduriyet',
        'çalışmıyor', 'kötü', 'ceza', 'patlak', 'gürültü', 'başıboş', 'yavaş', 'berbat',
        'aradım', 'ulaşılamıyor', 'ihmal', 'toz', 'çamur', 'yetersiz', 'pahalı', 'sorun',
        'problem', 'ariza', 'arıza', 'patladı', 'patlak', 'kokuyor', 'akmıyor',
    ];

    /**
     * Metni tokenize eder, hashtag ve anlamlı kelimeleri çıkarır, duygu puanını hesaplar.
     *
     * @return array{
     *     hashtags: list<string>,
     *     terms: list<string>,
     *     sentiment: 'positive'|'negative'|'neutral',
     *     is_complaint: bool
     * }
     */
    public function analyzeText(string $text): array
    {
        $normalized = mb_strtolower($text, 'UTF-8');

        // 1. Hashtag'leri yakala (#sukesintisi, #kecioren vb.)
        preg_match_all('/#([a-z0-9_çğıöşü]+)/u', $normalized, $hashtagMatches);
        $hashtags = array_unique($hashtagMatches[0] ?? []);

        // 2. Noktalama işaretlerini ve URL'leri temizle
        $cleanText = preg_replace('/https?:\/\/\S+/', '', $normalized);
        $cleanText = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', (string) $cleanText);

        $rawTokens = preg_split('/\s+/u', (string) $cleanText, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        // 3. Stop-words filtreleme ve uzunluk kontrolü (>= 3 karakter)
        $terms = [];
        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($rawTokens as $token) {
            if (mb_strlen($token, 'UTF-8') < 3 || in_array($token, $this->stopWords, true)) {
                continue;
            }

            $terms[] = $token;

            if (in_array($token, $this->positiveWords, true)) {
                $positiveCount++;
            }

            if (in_array($token, $this->negativeWords, true)) {
                $negativeCount++;
            }
        }

        // Hashtag'lerde şikayet kontrolü (Örn: #sukesintisi)
        foreach ($hashtags as $tag) {
            foreach ($this->negativeWords as $neg) {
                if (str_contains($tag, $neg)) {
                    $negativeCount += 2;
                }
            }
        }

        $sentiment = 'neutral';
        $isComplaint = false;

        if ($negativeCount > $positiveCount) {
            $sentiment = 'negative';
            $isComplaint = true;
        } elseif ($positiveCount > $negativeCount) {
            $sentiment = 'positive';
        }

        return [
            'hashtags' => array_values($hashtags),
            'terms' => array_values(array_unique($terms)),
            'sentiment' => $sentiment,
            'is_complaint' => $isComplaint,
        ];
    }

    /**
     * Tüm ilçeler için son 24 saatin trendlerini ve duygu endeksini derler.
     */
    public function processDailyDistrictAnalytics(?int $targetLocationId = null): void
    {
        $today = now()->toDateString();
        $since = now()->subDay();

        $locationsQuery = Location::query();
        if ($targetLocationId !== null) {
            $locationsQuery->where('id', $targetLocationId);
        }

        /** @var Collection<int, Location> $locations */
        $locations = $locationsQuery->get();

        foreach ($locations as $location) {
            // İlgili ilçenin son 24 saatteki herkese açık gönderileri
            $posts = Post::query()
                ->where('location_id', $location->id)
                ->where('created_at', '>=', $since)
                ->where('is_hidden', false)
                ->get(['id', 'content']);

            if ($posts->isEmpty()) {
                continue;
            }

            $termFrequencies = [];
            $positiveHits = [];
            $negativeHits = [];

            $totalPosts = $posts->count();
            $posPostsCount = 0;
            $negPostsCount = 0;
            $complaintsList = [];

            foreach ($posts as $post) {
                $analysis = $this->analyzeText($post->content);

                if ($analysis['sentiment'] === 'positive') {
                    $posPostsCount++;
                } elseif ($analysis['sentiment'] === 'negative') {
                    $negPostsCount++;
                }

                // Terim ve hashtag frekanslarını topla
                $allTerms = array_merge($analysis['hashtags'], $analysis['terms']);
                foreach ($allTerms as $term) {
                    $isHashtag = str_starts_with($term, '#');
                    $termFrequencies[$term] = ($termFrequencies[$term] ?? 0) + 1;

                    if ($analysis['sentiment'] === 'positive') {
                        $positiveHits[$term] = ($positiveHits[$term] ?? 0) + 1;
                    } elseif ($analysis['sentiment'] === 'negative') {
                        $negativeHits[$term] = ($negativeHits[$term] ?? 0) + 1;
                        if ($isHashtag || in_array($term, $this->negativeWords, true)) {
                            $complaintsList[$term] = ($complaintsList[$term] ?? 0) + 1;
                        }
                    }
                }
            }

            // 1. district_trends tablosunu güncelle
            arsort($termFrequencies);
            $topTerms = array_slice($termFrequencies, 0, 50, true);

            foreach ($topTerms as $term => $freq) {
                DistrictTrend::query()->updateOrCreate(
                    [
                        'location_id' => $location->id,
                        'term' => $term,
                        'date' => $today,
                    ],
                    [
                        'is_hashtag' => str_starts_with($term, '#'),
                        'frequency' => $freq,
                        'positive_hits' => $positiveHits[$term] ?? 0,
                        'negative_hits' => $negativeHits[$term] ?? 0,
                    ]
                );
            }

            // 2. district_sentiment_indexes tablosunu güncelle
            $evaluatedCount = $posPostsCount + $negPostsCount;
            $posScore = $evaluatedCount > 0 ? round(($posPostsCount / $evaluatedCount) * 100, 2) : 50.00;
            $negScore = $evaluatedCount > 0 ? round(($negPostsCount / $evaluatedCount) * 100, 2) : 50.00;

            arsort($complaintsList);
            $topComplaints = array_keys(array_slice($complaintsList, 0, 5, true));

            DistrictSentimentIndex::query()->updateOrCreate(
                [
                    'location_id' => $location->id,
                    'date' => $today,
                ],
                [
                    'positive_score' => $posScore,
                    'negative_score' => $negScore,
                    'total_posts_analyzed' => $totalPosts,
                    'total_complaints_detected' => $negPostsCount,
                    'top_complaint_topics' => $topComplaints,
                ]
            );

            // İlçe analitik önbelleğini temizle
            $slug = Str::slug($location->district, '-', 'tr');
            Cache::forget("analytics:pulse:{$slug}");
            Cache::forget("analytics:pulse:id_{$location->id}");
        }
    }

    /**
     * B2B Raporlama API'si için ilçenin haftalık nabız ve gündem raporunu döner.
     * 15 dakikalık önbellek ile korunur.
     *
     * @return array<string, mixed>
     */
    public function getDistrictPulseReport(string $districtSlug): array
    {
        return Cache::remember("analytics:pulse:{$districtSlug}", 900, function () use ($districtSlug) {
            // Slug üzerinden lokasyon tespiti
            /** @var Location|null $location */
            $location = Location::query()
                ->get()
                ->first(fn ($loc) => Str::slug($loc->district, '-', 'tr') === $districtSlug || Str::slug($loc->city . '-' . $loc->district, '-', 'tr') === $districtSlug);

            if (! $location) {
                // Varsayılan ilk lokasyon
                $location = Location::query()->firstOrFail();
            }

            $today = now()->toDateString();
            $lastWeek = now()->subDays(7)->toDateString();

            // Güncel duygu endeksi
            /** @var DistrictSentimentIndex|null $latestSentiment */
            $latestSentiment = DistrictSentimentIndex::query()
                ->where('location_id', $location->id)
                ->orderByDesc('date')
                ->first();

            // Haftalık en çok konuşulan 10 terim / hashtag
            $topTrends = DistrictTrend::query()
                ->where('location_id', $location->id)
                ->where('date', '>=', $lastWeek)
                ->select('term', 'is_hashtag', DB::raw('SUM(frequency) as total_volume'), DB::raw('SUM(negative_hits) as total_complaints'))
                ->groupBy('term', 'is_hashtag')
                ->orderByDesc('total_volume')
                ->take(10)
                ->get()
                ->map(fn ($t) => [
                    'term' => $t->term,
                    'is_hashtag' => (bool) $t->is_hashtag,
                    'volume' => (int) $t->total_volume,
                    'complaints_volume' => (int) $t->total_complaints,
                    'sentiment_ratio' => $t->total_volume > 0 ? round((((int) $t->total_volume - (int) $t->total_complaints) / (int) $t->total_volume) * 100, 1) : 100.0,
                ]);

            // Son 7 günün günlük duygu grafiği serisi
            $weeklyHistory = DistrictSentimentIndex::query()
                ->where('location_id', $location->id)
                ->where('date', '>=', $lastWeek)
                ->orderBy('date', 'asc')
                ->get(['date', 'positive_score', 'negative_score', 'total_posts_analyzed'])
                ->map(fn ($row) => [
                    'date' => $row->date->format('d.m'),
                    'positive' => $row->positive_score,
                    'negative' => $row->negative_score,
                    'posts_count' => $row->total_posts_analyzed,
                ]);

            return [
                'location' => [
                    'id' => $location->id,
                    'city' => $location->city,
                    'district' => $location->district,
                    'slug' => Str::slug($location->district, '-', 'tr'),
                ],
                'pulse_summary' => [
                    'positive_score' => $latestSentiment?->positive_score ?? 65.0,
                    'negative_score' => $latestSentiment?->negative_score ?? 35.0,
                    'total_analyzed_posts' => $latestSentiment?->total_posts_analyzed ?? 0,
                    'total_complaints' => $latestSentiment?->total_complaints_detected ?? 0,
                    'top_complaint_issues' => $latestSentiment?->top_complaint_topics ?? ['#ulaşım', '#çöp', '#park'],
                    'status_message' => ($latestSentiment?->positive_score ?? 65.0) >= 60.0
                        ? 'İlçede genel memnuniyet yüksek ve gündem sakin.'
                        : 'İlçede belediye hizmetleri veya altyapı şikayetlerinde artış gözleniyor.',
                ],
                'top_10_trending_topics' => $topTrends,
                'weekly_sentiment_chart' => $weeklyHistory,
                'generated_at' => now()->toIso8601String(),
                'disclaimer' => 'Veriler KVKK ve 5651 Sayılı Kanun gereğince tamamen anonimleştirilmiş genel akış istatistikleridir.',
            ];
        });
    }
}

