<?php

declare(strict_types=1);

namespace App\Services\Marketplace;

use App\Models\MarketplaceItem;
use App\Models\User;
use App\Services\MediaService;
use App\Services\Moderation\ContentFilterService;
use App\Services\Reputation\ReputationService;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MarketplaceService
{
    public function __construct(
        protected MediaService $mediaService,
        protected ContentFilterService $contentFilterService,
        protected ReputationService $reputationService
    ) {}

    /**
     * Yeni mahalle pazarı ilanı oluşturur.
     *
     * @param array<string, mixed> $data
     * @param list<UploadedFile> $images
     */
    public function createItem(User $user, array $data, array $images = []): MarketplaceItem
    {
        $locationId = ! empty($data['location_id'])
            ? (int) $data['location_id']
            : (int) ($user->location_id ?? 1);

        $isFree = ! empty($data['is_free']) || ((float) ($data['price'] ?? 0) <= 0);
        $price = $isFree ? 0.00 : (float) $data['price'];

        // Görselleri WebP formatında optimize edip Cloudflare R2 / yerel diske yükle
        $imagePaths = [];
        if (! empty($images)) {
            $imagePaths = $this->mediaService->uploadMultipleImages($images, 'marketplace');
        }

        // İçerik ve küfür filtresi
        $cleanTitle = $this->contentFilterService->inspectAndSanitize((string) $data['title']);
        $cleanDesc = $this->contentFilterService->inspectAndSanitize((string) $data['description']);

        /** @var MarketplaceItem $item */
        $item = MarketplaceItem::query()->create([
            'user_id' => $user->id,
            'location_id' => $locationId,
            'title' => $cleanTitle,
            'description' => $cleanDesc,
            'price' => $price,
            'category' => (string) ($data['category'] ?? 'diger'),
            'images' => ! empty($imagePaths) ? $imagePaths : null,
            'status' => 'active',
            'is_free' => $isFree,
        ]);

        // İtibar Ödülü: Mahalle içinde paylaşım yapana puan
        // Askıda eşya / ücretsiz dayanışma ilanlarına +5 puan, normal ilana +3 puan
        $rewardPoints = $isFree ? 5 : 3;
        $rewardReason = $isFree
            ? 'Mahalle Pazarı: Askıda / Ücretsiz eşya paylaşıldı'
            : 'Mahalle Pazarı: Yeni ilan yayınlandı';
        $this->reputationService->adjustPoints($user->id, 'marketplace_item_created', $rewardPoints, $rewardReason);

        // Yakınındaki ilanlar önbelleğini temizle
        Cache::forget("marketplace:nearby:{$locationId}");

        return $item->load([
            'user:id,name,username,avatar_path',
            'user.reputation',
            'location:id,city,district',
        ]);
    }

    /**
     * İlan durumunu günceller (Yayında, Rezerve Edildi, Satıldı).
     */
    public function updateStatus(User $user, int $itemId, string $status): MarketplaceItem
    {
        /** @var MarketplaceItem $item */
        $item = MarketplaceItem::query()->findOrFail($itemId);

        if ($item->user_id !== $user->id && ! $user->isSuperAdmin()) {
            throw new AccessDeniedHttpException('Bu ilanı güncelleme yetkiniz bulunmamaktadır.');
        }

        $oldStatus = $item->status;
        $item->update(['status' => $status]);

        // Eşya mahalleliye satıldığında / verildiğinde başarı puanı (+5 puan)
        if ($oldStatus !== 'sold' && $status === 'sold') {
            $this->reputationService->adjustPoints(
                $user->id,
                'marketplace_item_sold',
                5,
                'Mahalle içi eşya devri başarıyla tamamlandı'
            );
        }

        Cache::forget("marketplace:nearby:{$item->location_id}");

        return $item;
    }

    /**
     * İlçeye ait pazar ilanlarını listeler (Filtreli).
     *
     * @return CursorPaginator<MarketplaceItem>
     */
    public function getNeighborhoodItems(
        int $locationId,
        ?string $category = null,
        bool $onlyFree = false,
        ?string $status = 'active',
        ?string $cursor = null,
        int $perPage = 15
    ): CursorPaginator {
        $query = MarketplaceItem::query()
            ->where('location_id', $locationId)
            ->with([
                'user:id,name,username,avatar_path',
                'user.reputation',
                'location:id,city,district',
            ]);

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($onlyFree) {
            $query->where('is_free', true);
        }

        if (! empty($category) && $category !== 'all') {
            $query->where('category', $category);
        }

        // İndeks: (location_id, status, created_at desc)
        return $query->orderByDesc('id')
            ->cursorPaginate(perPage: $perPage, cursor: $cursor);
    }

    /**
     * Mahalle akışına her 5-6 gönderide bir yerleştirilen "Yakınındaki İlanlar" mini karuseli.
     * 60 saniyelik Redis/File önbelleği ile korunur.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getNearbyCarouselItems(int $locationId, int $limit = 6): Collection
    {
        $cacheKey = "marketplace:nearby:{$locationId}";

        return Cache::remember($cacheKey, 60, function () use ($locationId, $limit) {
            return MarketplaceItem::query()
                ->where('location_id', $locationId)
                ->where('status', 'active')
                ->orderByDesc('id')
                ->with(['user:id,name,username,avatar_path'])
                ->take($limit)
                ->get()
                ->map(function (MarketplaceItem $item) {
                    $mediaUrls = [];
                    if (! empty($item->images) && is_array($item->images)) {
                        foreach ($item->images as $path) {
                            $mediaUrls[] = $this->mediaService->getUrl((string) $path);
                        }
                    }

                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'price_formatted' => $item->formatted_price,
                        'category_label' => $item->category_label,
                        'is_free' => $item->is_free,
                        'image_url' => $mediaUrls[0] ?? null,
                        'created_at_human' => $item->created_at?->diffForHumans(),
                        'seller' => [
                            'name' => $item->user?->name,
                            'username' => $item->user?->username,
                        ],
                    ];
                });
        });
    }
}

