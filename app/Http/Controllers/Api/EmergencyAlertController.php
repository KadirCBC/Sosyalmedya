<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmergencyAlert;
use App\Services\Emergency\EmergencyAlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmergencyAlertController extends Controller
{
    public function __construct(
        protected EmergencyAlertService $alertService
    ) {}

    /**
     * İlgili ilçedeki aktif acil durum ilanlarını döner (Kırmızı Akış Endpoint'i).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $locationId = (int) $request->input('location_id', $user?->location_id ?? 1);

        $alerts = $this->alertService->getActiveAlertsForLocation($locationId);

        $data = $alerts->map(function (EmergencyAlert $a) {
            return [
                'id' => $a->id,
                'alert_type' => $a->alert_type,
                'type_label' => $a->type_label,
                'icon' => $a->icon,
                'title' => $a->title,
                'description' => $a->description,
                'contact_phone' => $a->contact_phone,
                'latitude' => $a->latitude,
                'longitude' => $a->longitude,
                'created_at_human' => $a->created_at?->diffForHumans(),
                'verified_by' => $a->verifier?->name ?? 'Meydan Moderasyonu',
                'theme' => $a->theme_classes,
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $data->count(),
            'alerts' => $data,
        ]);
    }

    /**
     * Yeni acil durum bildirimi oluşturur.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'alert_type' => 'required|in:kayip_ilan,afet_yardim,kan_bagisi,guvenlik_uyari',
            'title' => 'required|string|max:150',
            'description' => 'required|string|max:2000',
            'contact_phone' => 'required|string|max:30',
            'location_id' => 'required|exists:locations,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $user = $request->user();
        $alert = $this->alertService->createAlert($user, $validated);

        $message = $alert->is_verified
            ? '🚨 Acil durum uyarısı doğrulandı ve ilçe genelinde yayına alındı!'
            : 'Acil durum bildiriminiz alındı. Moderatör onayının ardından ilçe akışında kırmızı uyarı olarak yayınlanacaktır.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'alert_id' => $alert->id,
            'is_verified' => $alert->is_verified,
        ], 201);
    }

    /**
     * Moderatör onayı ile ilanı doğrular ve gecikmesiz bildirim fırlatır.
     */
    public function verify(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $alert = $this->alertService->verifyAlert($user, $id);

        return response()->json([
            'success' => true,
            'message' => 'Acil durum uyarısı onaylandı ve ilçe sakinlerinin kilit ekranına bildirim gönderildi.',
            'alert' => $alert,
        ]);
    }

    /**
     * Acil durumu 'Çözüldü' olarak kapatır.
     */
    public function resolve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $alert = $this->alertService->resolveAlert($user, $id);

        return response()->json([
            'success' => true,
            'message' => 'Acil durum ilanı kapatıldı.',
            'status' => $alert->status,
        ]);
    }
}

