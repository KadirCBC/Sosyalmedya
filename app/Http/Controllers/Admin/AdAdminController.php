<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AdStatus;
use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdAdminController extends Controller
{
    /**
     * Sponsorlu ilan ve reklam listesi.
     */
    public function index(Request $request): View
    {
        $ads = Ad::query()
            ->with(['user:id,name,username', 'location:id,city,district'])
            ->orderByRaw("FIELD(status, 'pending', 'active', 'rejected', 'expired')")
            ->orderByDesc('id')
            ->paginate(15);

        // Kümülatif Reklam İstatistikleri
        $totalImpressions = Ad::query()->sum('impressions_count');
        $totalClicks = Ad::query()->sum('clicks_count');
        $totalBudget = Ad::query()->where('status', AdStatus::ACTIVE)->sum('budget');
        $averageCtr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0.0;

        return view('admin.ads.index', [
            'ads' => $ads,
            'totalImpressions' => $totalImpressions,
            'totalClicks' => $totalClicks,
            'totalBudget' => $totalBudget,
            'averageCtr' => $averageCtr,
        ]);
    }

    /**
     * İlanı onayla ve yayına al.
     */
    public function approve(int $id): RedirectResponse
    {
        $ad = Ad::query()->findOrFail($id);

        $ad->update([
            'status' => AdStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays(30), // Varsayılan 30 gün yayın
        ]);

        return back()->with('success', "'{$ad->title}' başlıklı ilan onaylandı ve yayına alındı.");
    }

    /**
     * İlanı reddet.
     */
    public function reject(int $id): RedirectResponse
    {
        $ad = Ad::query()->findOrFail($id);

        $ad->update([
            'status' => AdStatus::REJECTED,
        ]);

        return back()->with('success', "'{$ad->title}' başlıklı ilan reddedildi.");
    }
}

