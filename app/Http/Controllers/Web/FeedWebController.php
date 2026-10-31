<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FeedWebController extends Controller
{
    /**
     * Ana sayfa çift akış (Feed) arayüzünü döndürür.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Kullanıcının kayıtlı olduğu lokasyon veya varsayılan lokasyon
        $location = $user?->location ?? Location::query()->first();

        $districtName = $location?->district ?? 'Kadıköy';
        $locationId = $location?->id ?? 1;

        return view('feed.index', [
            'districtName' => $districtName,
            'locationId' => $locationId,
        ]);
    }
}

