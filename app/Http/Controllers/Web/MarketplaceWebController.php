<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\MarketplaceItem;
use App\Services\Marketplace\MarketplaceService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketplaceWebController extends Controller
{
    public function __construct(
        protected MarketplaceService $marketplaceService,
        protected MediaService $mediaService
    ) {}

    /**
     * Mahalle Pazarı ana vitrini.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $locationId = (int) $request->input('location_id', $user?->location_id ?? 1);
        $location = Location::query()->find($locationId) ?? Location::query()->first();

        return view('marketplace.index', [
            'location' => $location,
            'user' => $user,
        ]);
    }

    /**
     * Yeni ilan verme sayfası.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $locations = Location::query()->orderBy('city')->orderBy('district')->get();
        $defaultLocation = $user ? $locations->firstWhere('id', $user->location_id) : $locations->first();

        return view('marketplace.create', [
            'locations' => $locations,
            'defaultLocation' => $defaultLocation,
            'user' => $user,
        ]);
    }

    /**
     * İlan detay sayfası.
     */
    public function show(Request $request, int $id): View
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

        return view('marketplace.show', [
            'item' => $item,
            'imageUrls' => $imageUrls,
            'currentUser' => $request->user(),
        ]);
    }
}

