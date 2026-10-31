<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\Analytics\DistrictPulseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DistrictAnalyticsController extends Controller
{
    public function __construct(
        protected DistrictPulseService $pulseService
    ) {}

    /**
     * B2B Yerel Nabız & İlçe Duygu Raporu API Endpoint'i.
     * Kurumlar, belediyeler ve yerel basın için haftalık trendleri ve memnuniyet endeksini döner.
     */
    public function pulse(Request $request, string $district_slug): JsonResponse
    {
        $report = $this->pulseService->getDistrictPulseReport($district_slug);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Analitik raporu alınabilecek aktif pilot ilçe listesini döner.
     */
    public function availableDistricts(): JsonResponse
    {
        $districts = Location::query()
            ->orderBy('city')
            ->orderBy('district')
            ->take(100)
            ->get()
            ->map(fn (Location $loc) => [
                'id' => $loc->id,
                'city' => $loc->city,
                'district' => $loc->district,
                'slug' => Str::slug($loc->district, '-', 'tr'),
                'api_url' => url("/api/v1/analytics/pulse/" . Str::slug($loc->district, '-', 'tr')),
            ]);

        return response()->json([
            'success' => true,
            'data' => $districts,
        ]);
    }
}

