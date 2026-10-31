<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\ActivityLogService;
use App\Services\Moderation\BlockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockController extends Controller
{
    public function __construct(
        protected BlockService $blockService,
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * Kullanıcıyı engeller.
     */
    public function store(Request $request, int $userId): JsonResponse
    {
        $this->blockService->blockUser($request->user(), $userId);

        $this->activityLogService->log(
            request: $request,
            action: 'user_block',
            user: $request->user(),
            targetType: User::class,
            targetId: $userId
        );

        return response()->json([
            'message' => 'Kullanıcı engellendi. Bu kullanıcının içerikleri artık akışlarınızda görünmeyecektir.',
        ], Response::HTTP_OK);
    }

    /**
     * Kullanıcının engelini kaldırır.
     */
    public function destroy(Request $request, int $userId): JsonResponse
    {
        $this->blockService->unblockUser($request->user(), $userId);

        $this->activityLogService->log(
            request: $request,
            action: 'user_unblock',
            user: $request->user(),
            targetType: User::class,
            targetId: $userId
        );

        return response()->json([
            'message' => 'Kullanıcının engeli kaldırıldı.',
        ], Response::HTTP_OK);
    }
}

