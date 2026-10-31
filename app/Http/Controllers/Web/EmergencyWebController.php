<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EmergencyAlert;
use App\Models\Location;
use App\Services\Emergency\EmergencyAlertService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmergencyWebController extends Controller
{
    public function __construct(
        protected EmergencyAlertService $alertService
    ) {}

    /**
     * Acil durum bildirimi oluşturma sayfası.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $locations = Location::query()->orderBy('city')->orderBy('district')->get();
        $defaultLocation = $user ? $locations->firstWhere('id', $user->location_id) : $locations->first();

        return view('emergency.create', [
            'locations' => $locations,
            'defaultLocation' => $defaultLocation,
            'user' => $user,
        ]);
    }

    /**
     * Acil durum ilan detay sayfası.
     */
    public function show(int $id): View
    {
        /** @var EmergencyAlert $alert */
        $alert = EmergencyAlert::query()
            ->with(['user', 'location', 'verifier'])
            ->findOrFail($id);

        return view('emergency.show', [
            'alert' => $alert,
        ]);
    }
}

