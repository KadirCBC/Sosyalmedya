<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\Location;
use App\Services\Business\BusinessProfileService;
use App\Services\Business\BusinessSubscriptionService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessWebController extends Controller
{
    public function __construct(
        protected BusinessProfileService $businessProfileService,
        protected BusinessSubscriptionService $subscriptionService,
        protected MediaService $mediaService
    ) {}

    /**
     * Mahalle esnaf rehberi.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $locationId = (int) $request->input('location_id', $user?->location_id ?? 1);
        $location = Location::query()->find($locationId) ?? Location::query()->first();

        return view('business.index', [
            'location' => $location,
            'user' => $user,
        ]);
    }

    /**
     * İşletme profili vitrini (Menü, yorumlar, WhatsApp sipariş, yol tarifi).
     */
    public function show(Request $request, int $id): View
    {
        /** @var BusinessProfile $profile */
        $profile = BusinessProfile::query()
            ->with([
                'user:id,name,username,avatar_path',
                'location:id,city,district',
                'catalogItems' => fn ($q) => $q->where('is_available', true),
                'reviews.user:id,name,username,avatar_path,role',
                'reviews.user.reputation',
            ])
            ->findOrFail($id);

        return view('business.show', [
            'profile' => $profile,
            'currentUser' => $request->user(),
            'mediaService' => $this->mediaService,
        ]);
    }

    /**
     * Yeni esnaf kayıt ve işletme profili oluşturma formu.
     */
    public function register(Request $request): View
    {
        $user = $request->user();
        $locations = Location::query()->orderBy('city')->orderBy('district')->get();
        $defaultLocation = $user ? $locations->firstWhere('id', $user->location_id) : $locations->first();
        $existingProfile = $user ? $user->businessProfile : null;

        return view('business.register', [
            'locations' => $locations,
            'defaultLocation' => $defaultLocation,
            'user' => $user,
            'profile' => $existingProfile,
        ]);
    }

    /**
     * Esnaf SaaS abonelik (PayTR) sayfası.
     */
    public function subscription(Request $request, int $id): View
    {
        $user = $request->user();
        $profile = BusinessProfile::query()->findOrFail($id);

        return view('business.subscription', [
            'profile' => $profile,
            'user' => $user,
            'monthlyPrice' => BusinessSubscriptionService::MONTHLY_SUBSCRIPTION_PRICE,
        ]);
    }

    /**
     * Test / Sandbox ortamı için mock PayTR ödeme simülatörü.
     */
    public function subscriptionMock(int $id): View
    {
        $profile = BusinessProfile::query()->findOrFail($id);

        return view('business.mock-subscription', [
            'profile' => $profile,
            'amount' => BusinessSubscriptionService::MONTHLY_SUBSCRIPTION_PRICE,
        ]);
    }
}

