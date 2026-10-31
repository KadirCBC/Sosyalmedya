<?php

declare(strict_types=1);

namespace App\Services\Business;

use App\Models\BusinessCatalogItem;
use App\Models\BusinessProfile;
use App\Models\User;
use App\Services\MediaService;
use App\Services\Moderation\ContentFilterService;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BusinessProfileService
{
    public function __construct(
        protected MediaService $mediaService,
        protected ContentFilterService $contentFilterService
    ) {}

    /**
     * Esnaf işletme profilini oluşturur veya günceller.
     *
     * @param array<string, mixed> $data
     */
    public function createOrUpdateProfile(
        User $user,
        array $data,
        ?UploadedFile $logo = null,
        ?UploadedFile $cover = null
    ): BusinessProfile {
        $cleanName = $this->contentFilterService->inspectAndSanitize((string) $data['business_name']);
        $cleanAddress = $this->contentFilterService->inspectAndSanitize((string) $data['address']);

        $logoPath = null;
        if ($logo) {
            $logoPath = $this->mediaService->uploadSingleImage($logo, 'business/logos');
        }

        $coverPath = null;
        if ($cover) {
            $coverPath = $this->mediaService->uploadSingleImage($cover, 'business/covers');
        }

        /** @var BusinessProfile $profile */
        $profile = BusinessProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'location_id' => (int) ($data['location_id'] ?? $user->location_id ?? 1),
                'business_name' => $cleanName,
                'tax_number' => ! empty($data['tax_number']) ? (string) $data['tax_number'] : null,
                'category' => (string) ($data['category'] ?? 'diger'),
                'address' => $cleanAddress,
                'google_maps_url' => ! empty($data['google_maps_url']) ? (string) $data['google_maps_url'] : null,
                'phone' => (string) $data['phone'],
                'whatsapp_number' => ! empty($data['whatsapp_number']) ? (string) $data['whatsapp_number'] : (string) $data['phone'],
                'opening_hours' => $data['opening_hours'] ?? null,
                'logo_url' => $logoPath ?: ($user->businessProfile?->logo_url ?? null),
                'cover_url' => $coverPath ?: ($user->businessProfile?->cover_url ?? null),
            ]
        );

        return $profile->load(['user', 'location', 'catalogItems']);
    }

    /**
     * İşletme menüsüne / kataloğuna yeni ürün ekler.
     *
     * @param array<string, mixed> $data
     */
    public function addCatalogItem(BusinessProfile $profile, array $data, ?UploadedFile $image = null): BusinessCatalogItem
    {
        $imagePath = null;
        if ($image) {
            $imagePath = $this->mediaService->uploadSingleImage($image, 'business/catalog');
        }

        return BusinessCatalogItem::query()->create([
            'business_profile_id' => $profile->id,
            'name' => (string) $data['name'],
            'price' => (float) ($data['price'] ?? 0.00),
            'description' => ! empty($data['description']) ? (string) $data['description'] : null,
            'image_url' => $imagePath,
            'is_available' => true,
        ]);
    }

    /**
     * Menü / Katalog ürününü siler.
     */
    public function deleteCatalogItem(User $user, int $itemId): bool
    {
        /** @var BusinessCatalogItem $item */
        $item = BusinessCatalogItem::query()->with('businessProfile')->findOrFail($itemId);

        if ($item->businessProfile->user_id !== $user->id && ! $user->isSuperAdmin()) {
            throw new AccessDeniedHttpException('Bu ürünü silme yetkiniz bulunmamaktadır.');
        }

        return (bool) $item->delete();
    }

    /**
     * İlçedeki işletmeleri listeler.
     *
     * @return CursorPaginator<BusinessProfile>
     */
    public function getDistrictBusinesses(
        int $locationId,
        ?string $category = null,
        ?string $cursor = null,
        int $perPage = 15
    ): CursorPaginator {
        $query = BusinessProfile::query()
            ->where('location_id', $locationId)
            ->with(['user', 'location'])
            ->orderByDesc('rating_avg')
            ->orderByDesc('reviews_count');

        if (! empty($category) && $category !== 'all') {
            $query->where('category', $category);
        }

        return $query->cursorPaginate(perPage: $perPage, cursor: $cursor);
    }
}

