<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\Reputation\ReputationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function __construct(
        protected ReputationService $reputationService
    ) {}

    /**
     * Belirli bir ilçenin "Haftanın Komşuları" liderlik tablosunu döner.
     */
    public function index(Request $request, int $locationId): JsonResponse
    {
        $location = Location::query()->findOrFail($locationId);
        $leaderboard = $this->reputationService->getDistrictLeaderboard($location->id, 10);

        return response()->json([
            'success' => true,
            'location' => [
                'id' => $location->id,
                'city' => $location->city,
                'district' => $location->district,
            ],
            'leaderboard' => $leaderboard,
        ]);
    }
}

