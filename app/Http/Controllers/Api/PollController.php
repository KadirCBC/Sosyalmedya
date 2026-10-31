<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Services\Poll\PollService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PollController extends Controller
{
    public function __construct(
        protected PollService $pollService
    ) {}

    /**
     * Ankete oy kullanır.
     */
    public function vote(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'option_id' => ['required', 'integer'],
        ]);

        try {
            $pollData = $this->pollService->vote(
                user: $request->user(),
                pollId: $id,
                optionId: (int) $request->input('option_id')
            );

            return response()->json([
                'success' => true,
                'message' => 'Oyunuz başarıyla kaydedildi.',
                'poll' => $pollData,
            ], HttpResponse::HTTP_OK);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Tekil anket detayını ve güncel oy dağılımını döner.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var Poll $poll */
        $poll = Poll::query()
            ->with(['options', 'post.location'])
            ->findOrFail($id);

        $pollData = $this->pollService->formatPollData($poll, $request->user('sanctum'));

        return response()->json([
            'success' => true,
            'poll' => $pollData,
        ], HttpResponse::HTTP_OK);
    }

    /**
     * Anket sonuçlarını X, WhatsApp veya basında paylaşmak üzere
     * dinamik 1200x630 PNG infografik kartı olarak üretir ve döner.
     */
    public function card(Request $request, int $id): Response
    {
        $pngData = $this->pollService->generateInfographicCard($id);

        $disposition = $request->has('download') ? 'attachment; filename="meydan-anket-' . $id . '.png"' : 'inline';

        return response($pngData, HttpResponse::HTTP_OK, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => $disposition,
            'Cache-Control' => 'public, max-age=120',
        ]);
    }
}

