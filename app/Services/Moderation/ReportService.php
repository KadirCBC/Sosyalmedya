<?php

declare(strict_types=1);

namespace App\Services\Moderation;

use App\Enums\PostScope;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Services\FeedService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReportService
{
    /**
     * Otomatik akıştan gizleme eşik değeri (farklı kullanıcılardan gelen şikayet sayısı).
     */
    public const AUTO_HIDE_THRESHOLD = 5;

    public function __construct(
        protected FeedService $feedService
    ) {}

    /**
     * İçerik veya kullanıcı için şikayet kaydı oluşturur.
     * Eşik değeri aşılırsa gönderiyi otomatik olarak akıştan gizler.
     *
     * @throws ValidationException
     */
    public function submitReport(
        User $reporter,
        string $reportableType,
        int $reportableId,
        ReportReason $reason,
        ?string $description = null
    ): Report {
        // Kendi içeriğini şikayet etmeyi engelle
        if ($reportableType === Post::class) {
            $post = Post::query()->findOrFail($reportableId);
            if ($post->user_id === $reporter->id) {
                throw ValidationException::withMessages([
                    'reportable_id' => ['Kendi gönderinizi şikayet edemezsiniz.'],
                ]);
            }
        }

        // Önceden şikayet edilmiş mi kontrolü
        $alreadyReported = Report::query()
            ->where('reporter_id', $reporter->id)
            ->where('reportable_type', $reportableType)
            ->where('reportable_id', $reportableId)
            ->exists();

        if ($alreadyReported) {
            throw ValidationException::withMessages([
                'reportable_id' => ['Bu içeriği daha önce şikayet ettiniz. İnceleme devam etmektedir.'],
            ]);
        }

        return DB::transaction(function () use ($reporter, $reportableType, $reportableId, $reason, $description) {
            /** @var Report $report */
            $report = Report::query()->create([
                'reporter_id' => $reporter->id,
                'reportable_type' => $reportableType,
                'reportable_id' => $reportableId,
                'reason' => $reason,
                'description' => $description,
                'status' => ReportStatus::PENDING,
            ]);

            // Bekleyen şikayet sayısını kontrol et
            $pendingReportsCount = Report::query()
                ->where('reportable_type', $reportableType)
                ->where('reportable_id', $reportableId)
                ->where('status', ReportStatus::PENDING)
                ->count();

            // Eşik değerine (5 şikayet) ulaşıldıysa gönderiyi otomatik gizle
            if ($pendingReportsCount >= self::AUTO_HIDE_THRESHOLD && $reportableType === Post::class) {
                /** @var Post|null $post */
                $post = Post::query()->find($reportableId);

                if ($post && ! $post->is_hidden) {
                    $post->update(['is_hidden' => true]);

                    // Önbellekten kaldır
                    if ($post->scope === PostScope::LOCAL && $post->location_id !== null) {
                        $this->feedService->clearLocalFeedCache($post->location_id);
                    }
                }
            }

            return $report;
        });
    }
}

