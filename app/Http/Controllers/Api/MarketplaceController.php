<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceItem;
use App\Services\Marketplace\MarketplaceService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function __construct(
        protected MarketplaceService $marketplaceService,
        protected MediaService $mediaService
    ) {}

    /**
     * Mahalle pazarındaki ilanları listeler.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $locationId = (int) $request->input('location_id', $user?->location_id ?? 1);
        $category = $request->input('category');
        $onlyFree = $request->boolean('only_free', false);
        $cursor = $request->input('cursor');

        $paginator = $this->marketplaceService->getNeighborhoodItems(
            locationId: $locationId,
            category: $category,
            onlyFree: $onlyFree,
            status: 'active',
            cursor: $cursor,
            perPage: 15
        );

        $items = collect($paginator->items())->map(function (MarketplaceItem $item) {
            $imageUrls = [];
            if (! empty($item->images) && is_array($item->images)) {
                foreach ($item->images as $path) {
                    $imageUrls[] = $this->mediaService->getUrl((string) $path);
                }
            }

            return [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'price' => $item->price,
                'formatted_price' => $item->formatted_price,
                'category' => $item->category,
                'category_label' => $item->category_label,
                'status' => $item->status,
                'is_free' => $item->is_free,
                'images' => $imageUrls,
                'thumbnail_url' => $imageUrls[0] ?? null,
                'created_at_human' => $item->created_at?->diffForHumans(),
                'seller' => [
                    'id' => $item->user?->id,
                    'name' => $item->user?->name,
                    'username' => $item->user?->username,
                    'avatar_url' => $this->mediaService->getUrl($item->user?->avatar_path),
                    'reputation' => [
                        'points' => (int) ($item->user?->reputation?->points ?? 0),
                        'level' => $item->user?->reputation?->level ?? 'Yeni Komşu',
                        'badge_icon' => $item->user?->reputation?->badge_icon ?? '🌱',
                    ],
                ],
                'location' => [
                    'id' => $item->location?->id,
                    'city' => $item->location?->city,
                    'district' => $item->location?->district,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $items,
            'next_cursor' => $paginator->nextCursor()?->encode(),
            'has_more' => $paginator->hasMorePages(),
        ]);
    }

    /**
     * Yeni ilan oluşturur.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'required|string|max:2000',
            'price' => 'nullable|numeric|min:0',
            'category' => 'required|in:elektronik,ev_esyasi,giyim,kitap_hobi,diger',
            'location_id' => 'nullable|exists:locations,id',
            'is_free' => 'nullable|boolean',
            'images.*' => 'nullable|image|max:5120',
        ]);

        $user = $request->user();
        $images = $request->file('images', []);

        $item = $this->marketplaceService->createItem($user, $validated, is_array($images) ? $images : [$images]);

        return response()->json([
            'success' => true,
            'message' => 'İlanınız mahalle pazarına eklendi! 🛍️',
            'item_id' => $item->id,
        ], 201);
    }

    /**
     * İlan detayını döner.
     */
    public function show(int $id): JsonResponse
    {
        /** @var MarketplaceItem $item */
        $item = MarketplaceItem::query()
            ->with([
                'user:id,name,username,avatar_path',
                'user.reputation',
                'location:id,city,district',
            ])
            ->findOrFail($id);

        $imageUrls = [];
        if (! empty($item->images) && is_array($item->images)) {
            foreach ($item->images as $path) {
                $imageUrls[] = $this->mediaService->getUrl((string) $path);
            }
        }

        return response()->json([
            'success' => true,
            'item' => [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'price' => $item->price,
                'formatted_price' => $item->formatted_price,
                'category' => $item->category,
                'category_label' => $item->category_label,
                'status' => $item->status,
                'status_label' => $item->status_label,
                'is_free' => $item->is_free,
                'images' => $imageUrls,
                'created_at_human' => $item->created_at?->diffForHumans(),
                'seller' => [
                    'id' => $item->user?->id,
                    'name' => $item->user?->name,
                    'username' => $item->user?->username,
                    'avatar_url' => $this->mediaService->getUrl($item->user?->avatar_path),
                    'reputation' => [
                        'points' => (int) ($item->user?->reputation?->points ?? 0),
                        'level' => $item->user?->reputation?->level ?? 'Yeni Komşu',
                        'badge_icon' => $item->user?->reputation?->badge_icon ?? '🌱',
                    ],
                ],
                'location' => [
                    'id' => $item->location?->id,
                    'city' => $item->location?->city,
                    'district' => $item->location?->district,
                ],
            ],
        ]);
    }

    /**
     * İlan durumunu günceller (Yayında / Rezerve / Satıldı).
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,reserved,sold',
        ]);

        $item = $this->marketplaceService->updateStatus($request->user(), $id, $validated['status']);

        return response()->json([
            'success' => true,
            'message' => "İlan durumu '{$item->status_label}' olarak güncellendi.",
            'status' => $item->status,
        ]);
    }

    /**
     * Akış içine yerleştirilecek mini karusel verisi.
     */
    public function carousel(Request $request): JsonResponse
    {
        $user = $request->user();
        $locationId = (int) $request->input('location_id', $user?->location_id ?? 1);

        $items = $this->marketplaceService->getNearbyCarouselItems($locationId, 6);

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }
}

