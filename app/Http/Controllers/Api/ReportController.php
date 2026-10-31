<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ReportReason;
use App\Http\Controllers\Controller;
use App\Http\Requests\Moderation\CreateReportRequest;
use App\Models\Post;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use App\Services\Moderation\ReportService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Kullanıcı veya gönderi için şikayet kaydı oluşturur.
     */
    public function store(CreateReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $typeMap = [
            'post' => Post::class,
            'user' => User::class,
        ];

        $reportableClass = $typeMap[$validated['reportable_type']];
        $reason = ReportReason::from((string) $validated['reason']);

        $report = $this->reportService->submitReport(
            reporter: $request->user(),
            reportableType: $reportableClass,
            reportableId: (int) $validated['reportable_id'],
            reason: $reason,
            description: $validated['description'] ?? null
        );

        // 5651 Sayılı Kanun denetim günlüğü
        $this->activityLogService->log(
            request: $request,
            action: 'report_create',
            user: $request->user(),
            content: $validated['description'] ?? null,
            targetType: $reportableClass,
            targetId: (int) $validated['reportable_id']
        );

        return response()->json([
            'message' => 'Bildiriminiz alındı. Moderasyon ekibimiz tarafından incelenecektir.',
            'report_id' => $report->id,
        ], Response::HTTP_CREATED);
    }
}

