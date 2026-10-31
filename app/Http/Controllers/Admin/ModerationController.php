<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Services\FeedService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModerationController extends Controller
{
    public function __construct(
        protected FeedService $feedService
    ) {}

    /**
     * Şikayet edilen gönderileri moderatörün bölgesine göre listeler.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Post::query()
            ->withoutGlobalScopes()
            ->whereHas('reports', function ($q) {
                $q->where('status', ReportStatus::PENDING);
            })
            ->with([
                'user:id,name,username,avatar_path,suspended_until',
                'location:id,city,district',
                'reports' => function ($q) {
                    $q->where('status', ReportStatus::PENDING)
                        ->with('reporter:id,name,username');
                },
            ])
            ->withCount([
                'reports as pending_reports_count' => function ($q) {
                    $q->where('status', ReportStatus::PENDING);
                },
            ])
            ->orderByDesc('pending_reports_count');

        // Bölge moderatörü ise yalnızca kendi ilçesindeki gönderileri görür
        if ($user && $user->isLocalModerator() && $user->moderated_location_id !== null) {
            $query->where('location_id', $user->moderated_location_id);
        }

        $reportedPosts = $query->paginate(15);

        return view('admin.moderation.index', [
            'reportedPosts' => $reportedPosts,
            'currentUser' => $user,
        ]);
    }

    /**
     * Gönderiyi onayla: Şikayetleri reddeder, gönderiyi akışta tekrar görünür yapar.
     */
    public function approve(Request $request, int $postId): RedirectResponse
    {
        $user = $request->user();
        /** @var Post $post */
        $post = Post::query()->withoutGlobalScopes()->findOrFail($postId);

        if (! $user->canModerateLocation($post->location_id)) {
            abort(403, 'Bu bölgedeki gönderileri yönetme yetkiniz bulunmamaktadır.');
        }

        $post->update(['is_hidden' => false]);

        Report::query()
            ->where('reportable_type', Post::class)
            ->where('reportable_id', $post->id)
            ->where('status', ReportStatus::PENDING)
            ->update([
                'status' => ReportStatus::REJECTED,
                'reviewer_id' => $user->id,
            ]);

        if ($post->location_id !== null) {
            $this->feedService->clearLocalFeedCache($post->location_id);
        }

        return back()->with('success', 'Gönderi onaylandı ve yayına alındı.');
    }

    /**
     * Gönderiyi gizle: Gönderiyi akıştan kaldırır ve şikayetleri çözüldü olarak işaretler.
     */
    public function hide(Request $request, int $postId): RedirectResponse
    {
        $user = $request->user();
        /** @var Post $post */
        $post = Post::query()->withoutGlobalScopes()->findOrFail($postId);

        if (! $user->canModerateLocation($post->location_id)) {
            abort(403, 'Bu bölgedeki gönderileri yönetme yetkiniz bulunmamaktadır.');
        }

        $post->update(['is_hidden' => true]);

        Report::query()
            ->where('reportable_type', Post::class)
            ->where('reportable_id', $post->id)
            ->where('status', ReportStatus::PENDING)
            ->update([
                'status' => ReportStatus::RESOLVED,
                'reviewer_id' => $user->id,
            ]);

        if ($post->location_id !== null) {
            $this->feedService->clearLocalFeedCache($post->location_id);
        }

        return back()->with('success', 'Gönderi akıştan gizlendi.');
    }

    /**
     * İçeriği tamamen siler.
     */
    public function destroy(Request $request, int $postId): RedirectResponse
    {
        $user = $request->user();
        /** @var Post $post */
        $post = Post::query()->withoutGlobalScopes()->findOrFail($postId);

        if (! $user->canModerateLocation($post->location_id)) {
            abort(403, 'Bu bölgedeki gönderileri yönetme yetkiniz bulunmamaktadır.');
        }

        $locationId = $post->location_id;

        Report::query()
            ->where('reportable_type', Post::class)
            ->where('reportable_id', $post->id)
            ->update([
                'status' => ReportStatus::RESOLVED,
                'reviewer_id' => $user->id,
            ]);

        $post->delete();

        if ($locationId !== null) {
            $this->feedService->clearLocalFeedCache($locationId);
        }

        return back()->with('success', 'Gönderi kalıcı olarak silindi.');
    }

    /**
     * İhlal yapan kullanıcının hesabını 24 saat süreyle askıya alır.
     */
    public function suspendUser(Request $request, int $userId): RedirectResponse
    {
        $user = $request->user();
        $targetUser = User::query()->findOrFail($userId);

        if ($targetUser->isSuperAdmin()) {
            return back()->with('error', 'Yönetici hesapları askıya alınamaz.');
        }

        // Bölge moderatörü yalnızca kendi bölgesindeki kullanıcıları askıya alabilir
        if ($user->isLocalModerator() && (int) $targetUser->location_id !== (int) $user->moderated_location_id) {
            abort(403, 'Yalnızca kendi bölgenizdeki kullanıcıları askıya alabilirsiniz.');
        }

        $targetUser->update([
            'suspended_until' => now()->addHours(24),
        ]);

        return back()->with('success', "{$targetUser->name} (@{$targetUser->username}) 24 saat süreyle askıya alındı.");
    }
}

