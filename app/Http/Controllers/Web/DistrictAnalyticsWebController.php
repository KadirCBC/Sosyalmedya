<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\Analytics\DistrictPulseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DistrictAnalyticsWebController extends Controller
{
    public function __construct(
        protected DistrictPulseService $pulseService
    ) {}

    /**
     * B2B & Kurumsal Yerel Nabız Panosu görünümünü sunar.
     */
    public function pulse(Request $request, ?string $districtSlug = null): View
    {
        // İlçe parametresi yoksa varsayılan olarak Keçiören veya ilk lokasyon seçilir
        if (! $districtSlug) {
            $defaultLocation = Location::query()
                ->where('district', 'LIKE', '%Keçiören%')
                ->orWhere('district', 'LIKE', '%Kecioren%')
                ->first() ?? Location::query()->first();

            $districtSlug = $defaultLocation ? Str::slug($defaultLocation->district, '-', 'tr') : 'kecioren';
        }

        $report = $this->pulseService->getDistrictPulseReport($districtSlug);

        // İlçe seçici dropdown'ı için popüler/mevcut ilçeler
        $districts = Location::query()
            ->orderBy('city')
            ->orderBy('district')
            ->take(50)
            ->get(['id', 'city', 'district'])
            ->map(fn ($loc) => [
                'id' => $loc->id,
                'city' => $loc->city,
                'district' => $loc->district,
                'slug' => Str::slug($loc->district, '-', 'tr'),
            ]);

        return view('analytics.pulse', [
            'report' => $report,
            'districts' => $districts,
            'currentSlug' => $districtSlug,
        ]);
    }
}

