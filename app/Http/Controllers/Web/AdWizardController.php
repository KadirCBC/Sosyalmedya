<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Location;
use App\Services\Ad\AdWizardService;
use App\Services\Payment\PayTrPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdWizardController extends Controller
{
    protected AdWizardService $adWizardService;
    protected PayTrPaymentService $payTrService;

    public function __construct(AdWizardService $adWizardService, PayTrPaymentService $payTrService)
    {
        $this->adWizardService = $adWizardService;
        $this->payTrService = $payTrService;
    }

    /**
     * Show the 4-step self-service Ad Wizard.
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        
        // Fetch all active districts/locations grouped or sorted
        $locations = Location::query()
            ->orderBy('city')
            ->orderBy('district')
            ->get();

        // Merchant default location
        $defaultLocationId = $user ? $user->location_id : ($locations->first()?->id ?? 1);
        $defaultLocation = $locations->firstWhere('id', $defaultLocationId);

        return view('ads.wizard', [
            'locations' => $locations,
            'defaultLocation' => $defaultLocation,
            'user' => $user,
        ]);
    }

    /**
     * Store the draft ad and initiate the PayTR payment session.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:120',
            'content' => 'required|string|max:500',
            'image' => 'nullable|image|max:5120', // max 5MB
            'location_id' => 'required|exists:locations,id',
            'duration_days' => 'required|integer|min:1|max:90',
            'daily_budget' => 'required|numeric|min:20|max:10000',
            'cta_type' => 'required|in:whatsapp,call,maps,link',
            'cta_value' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $userId = $user ? $user->id : 1; // Fallback for guest merchant testing

        // 1. Create Pending Ad via AdWizardService
        $ad = $this->adWizardService->createPendingAd($userId, $validated, $request->file('image'));

        // 2. Request PayTR Payment Token / Iframe URL
        $paymentData = $this->payTrService->createPaymentToken($ad, $user, $request->ip());

        if (! $paymentData['success']) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ödeme altyapısına bağlanırken hata oluştu: ' . ($paymentData['error'] ?? 'Bilinmeyen hata'),
                ], 422);
            }

            return back()->withInput()->withErrors(['payment' => 'Ödeme oturumu başlatılamadı.']);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'ad_id' => $ad->id,
                'merchant_oid' => $ad->merchant_oid,
                'iframe_url' => $paymentData['iframe_url'],
                'token' => $paymentData['token'] ?? null,
                'amount_try' => number_format($ad->paid_amount, 2, ',', '.') . ' TL',
            ]);
        }

        return redirect()->away($paymentData['iframe_url']);
    }

    /**
     * Calculate reach dynamically via AJAX when merchant changes budget/duration.
     */
    public function calculateReach(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'daily_budget' => 'required|numeric|min:10',
            'duration_days' => 'required|integer|min:1',
            'location_id' => 'nullable|integer',
        ]);

        $reach = $this->adWizardService->calculateEstimatedReach(
            (float) $validated['daily_budget'],
            (int) $validated['duration_days'],
            isset($validated['location_id']) ? (int) $validated['location_id'] : null
        );

        return response()->json([
            'success' => true,
            'reach' => $reach,
        ]);
    }

    /**
     * Display order & campaign success confirmation page.
     */
    public function success(int $id): View
    {
        $ad = Ad::with('location')->findOrFail($id);

        return view('ads.success', [
            'ad' => $ad,
        ]);
    }

    /**
     * Sandbox mock iframe for local/development environments.
     */
    public function mockIframe(int $id): View
    {
        $ad = Ad::with(['location', 'user'])->findOrFail($id);

        return view('ads.mock-iframe', [
            'ad' => $ad,
        ]);
    }
}

