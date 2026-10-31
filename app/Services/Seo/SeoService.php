<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\Location;
use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SeoService
{
    /**
     * City slug ve District slug'a göre lokasyonu bulur (Önbellekli).
     */
    public function findLocationBySlugs(string $citySlug, string $districtSlug): ?Location
    {
        /** @var Collection<int, Location> $locations */
        $locations = Cache::rememberForever('seo:locations:all', function () {
            return Location::query()->get();
        });

        return $locations->first(function (Location $loc) use ($citySlug, $districtSlug) {
            return Str::slug($loc->city, '-', 'tr') === $citySlug
                && Str::slug($loc->district, '-', 'tr') === $districtSlug;
        });
    }

    /**
     * Tüm ilçelerin slug haritasını döner (Sitemap üretimi için).
     *
     * @return list<array{city_slug: string, district_slug: string, updated_at: string}>
     */
    public function getAllDistrictSlugs(): array
    {
        return Cache::remember('seo:district:slugs:map', 86400, function () {
            return Location::query()
                ->select(['city', 'district', 'updated_at'])
                ->get()
                ->map(fn ($loc) => [
                    'city_slug' => Str::slug($loc->city, '-', 'tr'),
                    'district_slug' => Str::slug($loc->district, '-', 'tr'),
                    'updated_at' => $loc->updated_at?->toAtomString() ?? now()->toAtomString(),
                ])
                ->all();
        });
    }

    /**
     * Belirtilen koordinatlar için anlık yerel hava durumunu çeker (1 saat önbellekli).
     * Open-Meteo ücretsiz ve anahtarsız API kullanır; hata durumunda varsayılan değer döner.
     *
     * @return array{temp: int, desc: string, icon: string}
     */
    public function getLocalWeather(Location $location): array
    {
        $cacheKey = "seo:weather:loc:{$location->id}";

        return Cache::remember($cacheKey, 3600, function () use ($location) {
            try {
                $lat = $location->latitude;
                $lng = $location->longitude;
                $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&current=temperature_2m,weather_code";

                $response = Http::timeout(3)->get($url);
                if ($response->successful()) {
                    $data = $response->json();
                    $temp = (int) round((float) ($data['current']['temperature_2m'] ?? 18));
                    $code = (int) ($data['current']['weather_code'] ?? 0);

                    return $this->mapWeatherCode($temp, $code);
                }
            } catch (\Throwable $e) {
                // API erişilemezse hata fırlatma, zarifçe varsayılan dön
            }

            return [
                'temp' => 19,
                'desc' => 'Açık / Ilık',
                'icon' => '☀️',
            ];
        });
    }

    /**
     * WMO Weather Code'u Türkçe açıklamaya ve ikona çevirir.
     *
     * @return array{temp: int, desc: string, icon: string}
     */
    protected function mapWeatherCode(int $temp, int $code): array
    {
        [$desc, $icon] = match (true) {
            $code === 0 => ['Güneşli / Açık', '☀️'],
            in_array($code, [1, 2, 3]) => ['Parçalı Bulutlu', '⛅'],
            in_array($code, [45, 48]) => ['Sisli', '🌫️'],
            in_array($code, [51, 53, 55, 61, 63, 65, 80, 81, 82]) => ['Yağmurlu', '🌧️'],
            in_array($code, [71, 73, 75, 85, 86]) => ['Kar Yağışlı', '❄️'],
            in_array($code, [95, 96, 99]) => ['Gök Gürültülü Fırtına', '⛈️'],
            default => ['Açık', '🌤️'],
        };

        return [
            'temp' => $temp,
            'desc' => $desc,
            'icon' => $icon,
        ];
    }

    /**
     * İlçe açılış sayfası için Schema.org JSON-LD yapılandırılmış verisini üretir.
     * Google'ın DiscussionForumPosting ve Breadcrumb standartlarına tam uyumludur.
     *
     * @param Collection<int, Post> $posts
     */
    public function generateDistrictSchema(Location $location, Collection $posts, string $canonicalUrl): string
    {
        $postItems = [];
        foreach ($posts as $post) {
            $authorName = $post->user->name ?? 'Meydan Sakini';
            $postItems[] = [
                '@type' => 'DiscussionForumPosting',
                '@id' => url("/posts/{$post->id}"),
                'headline' => Str::limit($post->content, 80),
                'articleBody' => $post->content,
                'datePublished' => $post->created_at?->toIso8601String(),
                'author' => [
                    '@type' => 'Person',
                    'name' => $authorName,
                    'url' => url("/u/" . ($post->user->username ?? '')),
                ],
                'interactionStatistic' => [
                    [
                        '@type' => 'InteractionCounter',
                        'interactionType' => 'https://schema.org/LikeAction',
                        'userInteractionCount' => (int) $post->likes_count,
                    ],
                    [
                        '@type' => 'InteractionCounter',
                        'interactionType' => 'https://schema.org/CommentAction',
                        'userInteractionCount' => (int) $post->comments_count,
                    ],
                ],
            ];
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                // 1. BreadcrumbList (Ekmek Kırıntısı Navigasyonu)
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        [
                            '@type' => 'ListItem',
                            'position' => 1,
                            'name' => 'Ana Sayfa',
                            'item' => url('/'),
                        ],
                        [
                            '@type' => 'ListItem',
                            'position' => 2,
                            'name' => $location->city,
                            'item' => url("/yerel/" . Str::slug($location->city, '-', 'tr')),
                        ],
                        [
                            '@type' => 'ListItem',
                            'position' => 3,
                            'name' => "{$location->district} Canlı Akış",
                            'item' => $canonicalUrl,
                        ],
                    ],
                ],
                // 2. City / AdministrativeArea
                [
                    '@type' => 'AdministrativeArea',
                    'name' => "{$location->city} {$location->district}",
                    'description' => "{$location->city} ili {$location->district} ilçesi canlı yerel gündemi, belediye duyuruları ve mahalle paylaşımları.",
                    'geo' => [
                        '@type' => 'GeoCoordinates',
                        'latitude' => (float) $location->latitude,
                        'longitude' => (float) $location->longitude,
                    ],
                    'url' => $canonicalUrl,
                ],
                // 3. DiscussionForumPosting Listesi
                [
                    '@type' => 'CollectionPage',
                    'name' => "{$location->district} ({$location->city}) Mahalle Gündemi ve Yerel Paylaşımlar",
                    'url' => $canonicalUrl,
                    'hasPart' => $postItems,
                ],
            ],
        ];

        return (string) json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}

