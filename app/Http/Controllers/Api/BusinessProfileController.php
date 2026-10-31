<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Services\Business\BusinessProfileService;
use App\Services\Business\BusinessReviewService;
use App\Services\Business\BusinessSubscriptionService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessProfileController extends Controller
{
    public function __construct(
        protected BusinessProfileService $businessProfileService,
        protected BusinessReviewService $businessReviewService,
        protected BusinessSubscriptionService $subscriptionService,
        protected MediaService $mediaService
    ) {}

    /**
     * İlçedeki esnaf işletmelerini listeler.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $locationId = (int) $request->input('location_id', $user?->location_id ?? 1);
        $category = $request->input('category');
        $cursor = $request->input('cursor');

        $paginator = $this->businessProfileService->getDistrictBusinesses(
            locationId: $locationId,
            category: $category,
            cursor: $cursor,
            perPage: 15
        );

        $businesses = collect($paginator->items())->map(function (BusinessProfile $b) {
            return [
                'id' => $b->id,
                'business_name' => $b->business_name,
                'category' => $b->category,
                'category_label' => $b->category_label,
                'address' => $b->address,
                'phone' => $b->phone,
                'whatsapp_url' => $b->whatsapp_order_url,
                'logo_url' => $this->mediaService->getUrl($b->logo_url),
                'cover_url' => $this->mediaService->getUrl($b->cover_url),
                'is_verified' => $b->is_verified,
                'rating_avg' => $b->rating_avg,
                'reviews_count' => $b->reviews_count,
                'is_subscription_active' => $b->isSubscriptionActive(),
            ];
        });

        return response()->json([
            'success' => true,
            'businesses' => $businesses,
            'next_cursor' => $paginator->nextCursor()?->encode(),
            'has_more' => $paginator->hasMorePages(),
        ]);
    }

    /**
     * İşletme profil detayını katalog ve yorumlarıyla döner.
     */
    public function show(int $id): JsonResponse
    {
        /** @var BusinessProfile $profile */
        $profile = BusinessProfile::query()
            ->with([
                'user:id,name,username,avatar_path',
                'location:id,city,district',
                'catalogItems' => fn ($q) => $q->where('is_available', true),
                'reviews.user:id,name,username,avatar_path',
            ])
            ->findOrFail($id);

        $catalog = $profile->catalogItems->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
            'formatted_price' => $item->formatted_price,
            'description' => $item->description,
            'image_url' => $this->mediaService->getUrl($item->image_url),
        ]);

        $reviews = $profile->reviews->map(fn ($rev) => [
            'id' => $rev->id,
            'rating' => $rev->rating,
            'comment' => $rev->comment,
            'created_at_human' => $rev->created_at?->diffForHumans(),
            'user' => [
                'id' => $rev->user?->id,
                'name' => $rev->user?->name,
                'avatar_url' => $this->mediaService->getUrl($rev->user?->avatar_path),
            ],
        ]);

        return response()->json([
            'success' => true,
            'profile' => [
                'id' => $profile->id,
                'business_name' => $profile->business_name,
                'category' => $profile->category,
                'category_label' => $profile->category_label,
                'address' => $profile->address,
                'google_maps_url' => $profile->google_maps_url,
                'phone' => $profile->phone,
                'whatsapp_url' => $profile->whatsapp_order_url,
                'opening_hours' => $profile->opening_hours,
                'logo_url' => $this->mediaService->getUrl($profile->logo_url),
                'cover_url' => $this->mediaService->getUrl($profile->cover_url),
                'is_verified' => $profile->is_verified,
                'rating_avg' => $profile->rating_avg,
                'reviews_count' => $profile->reviews_count,
                'catalog' => $catalog,
                'reviews' => $reviews,
            ],
        ]);
    }

    /**
     * İşletme profili oluşturur veya günceller.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:120',
            'tax_number' => 'nullable|string|max:30',
            'category' => 'required|in:restoran_kafe,hizmet_usta,perakende_butik,diger',
            'address' => 'required|string|max:500',
            'google_maps_url' => 'nullable|url|max:500',
            'phone' => 'required|string|max:30',
            'whatsapp_number' => 'nullable|string|max:30',
            'location_id' => 'nullable|exists:locations,id',
            'logo' => 'nullable|image|max:5120',
            'cover' => 'nullable|image|max:5120',
        ]);

        $user = $request->user();
        $profile = $this->businessProfileService->createOrUpdateProfile(
            $user,
            $validated,
            $request->file('logo'),
            $request->file('cover')
        );

        return response()->json([
            'success' => true,
            'message' => 'İşletme profiliniz başarıyla kaydedildi!',
            'profile_id' => $profile->id,
        ]);
    }

    /**
     * İşletme menüsüne yeni ürün ekler.
     */
    public function addCatalogItem(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'image' => 'nullable|image|max:5120',
        ]);

        $user = $request->user();
        $profile = BusinessProfile::query()->where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $item = $this->businessProfileService->addCatalogItem($profile, $validated, $request->file('image'));

        return response()->json([
            'success' => true,
            'message' => 'Ürün menüye eklendi.',
            'item_id' => $item->id,
        ], 201);
    }

    /**
     * İşletmeye müşteri yorumu ve puanı ekler.
     */
    public function addReview(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'required|string|min:5|max:1000',
        ]);

        $user = $request->user();
        $review = $this->businessReviewService->addReview($user, $id, (int) $validated['rating'], $validated['comment']);

        return response()->json([
            'success' => true,
            'message' => 'Değerlendirmeniz için teşekkürler! Mahalle itibar puanı kazandınız. ⭐',
            'review_id' => $review->id,
        ], 201);
    }

    /**
     * Aylık sabit SaaS abonelik ödeme oturumu başlatır.
     */
    public function subscribe(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $profile = BusinessProfile::query()->where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $session = $this->subscriptionService->createSubscriptionSession($profile, $user, $request->ip());

        return response()->json([
            'success' => true,
            'price_try' => '499,00 TL / Ay',
            'iframe_url' => $session['iframe_url'],
        ]);
    }
}

