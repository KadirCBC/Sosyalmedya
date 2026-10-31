<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AdStatus;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Ad;
use App\Models\Location;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Yönetim Paneli Ana Gösterge Panosu.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Günlük Aktif Kullanıcı (DAU) - 5651 loglarından tekil kullanıcı sayısı
        $dauCount = ActivityLog::query()
            ->where('created_at', '>=', now()->startOfDay())
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // 2. Bekleyen Şikayet Sayısı (Moderatörün bölgesine göre filtrelenmiş)
        $pendingReportsQuery = Report::query()
            ->where('status', ReportStatus::PENDING);

        if ($user && $user->isLocalModerator() && $user->moderated_location_id !== null) {
            $pendingReportsQuery->whereHasMorph('reportable', [Post::class], function ($q) use ($user) {
                $q->withoutGlobalScopes()->where('location_id', $user->moderated_location_id);
            });
        }
        $pendingReportsCount = $pendingReportsQuery->count();

        // 3. Genel Platform İstatistikleri
        $totalUsersCount = User::query()->count();
        $totalPostsCount = Post::query()->withoutGlobalScopes()->count();
        $activeAdsCount = Ad::query()->active()->count();

        // 4. İlçelere Göre Son 7 Günlük Gönderi Yoğunluğu Sıralaması
        $districtDensity = Location::query()
            ->withCount(['posts' => function ($q) {
                $q->withoutGlobalScopes()->where('created_at', '>=', now()->subDays(7));
            }])
            ->orderByDesc('posts_count')
            ->take(8)
            ->get();

        // 5. Sunucu & Sistem Durumu Bilgisi (cPanel/Plesk PHP & Memory)
        $serverStats = [
            'php_version' => PHP_VERSION,
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            'opcache_enabled' => function_exists('opcache_get_status') && ! empty(opcache_get_status()),
        ];

        return view('admin.dashboard', [
            'dauCount' => $dauCount,
            'pendingReportsCount' => $pendingReportsCount,
            'totalUsersCount' => $totalUsersCount,
            'totalPostsCount' => $totalPostsCount,
            'activeAdsCount' => $activeAdsCount,
            'districtDensity' => $districtDensity,
            'serverStats' => $serverStats,
            'currentUser' => $user,
        ]);
    }
}

