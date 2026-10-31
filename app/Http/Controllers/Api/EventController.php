<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Event\EventService;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    public function __construct(
        protected EventService $eventService
    ) {}

    /**
     * İlçedeki yaklaşan etkinlikler listesi (Filtrelenebilir).
     */
    public function index(Request $request): JsonResponse
    {
        $locationId = (int) ($request->input('location_id') ?? $request->user('sanctum')?->location_id ?? 1);
        $category = $request->input('category');

        $events = $this->eventService->getUpcomingEvents($locationId, $category);

        return response()->json([
            'success' => true,
            'data' => $events,
        ]);
    }

    /**
     * "Bu Hafta Sonu Mahallede Ne Var?" etkinlik widget'ı API çıktısı.
     */
    public function weekend(Request $request): JsonResponse
    {
        $locationId = (int) ($request->input('location_id') ?? $request->user('sanctum')?->location_id ?? 1);

        $events = $this->eventService->getWeekendEvents($locationId);

        return response()->json([
            'success' => true,
            'location_id' => $locationId,
            'events' => $events,
        ]);
    }

    /**
     * Tekil etkinlik detayı.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var Event $event */
        $event = Event::query()
            ->with([
                'user:id,name,username,avatar_path',
                'location:id,city,district',
                'businessProfile:id,business_name,address,phone,logo_url,is_verified',
                'attendees.user:id,name,username,avatar_path',
            ])
            ->findOrFail($id);

        $user = $request->user('sanctum');
        $userStatus = null;
        if ($user) {
            $userStatus = $event->attendees->firstWhere('user_id', $user->id)?->status;
        }

        return response()->json([
            'success' => true,
            'event' => $event,
            'user_attendance' => [
                'status' => $userStatus,
                'is_attending' => $userStatus === 'attending',
                'is_interested' => $userStatus === 'interested',
            ],
            'category_meta' => $event->getCategoryMeta(),
        ]);
    }

    /**
     * Yeni bir yerel etkinlik / buluşma oluşturur.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'category' => ['required', 'in:spor,kultur_sanat,yardimlasma,hobi_oyun,belediye_duyuru'],
            'venue_name' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:255'],
            'event_date' => ['required', 'date', 'after:now'],
            'max_participants' => ['nullable', 'integer', 'min:2', 'max:1000'],
            'business_profile_id' => ['nullable', 'integer', 'exists:business_profiles,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
        ]);

        try {
            $event = $this->eventService->createEvent($request->user(), $validated);

            return response()->json([
                'success' => true,
                'message' => 'Etkinlik başarıyla oluşturuldu! Komşularınızı bekliyoruz.',
                'event' => $event,
            ], Response::HTTP_CREATED);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Etkinliğe katılım durumunu günceller ('attending' / 'interested' / iptal).
     */
    public function attend(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:attending,interested'],
        ]);

        try {
            $result = $this->eventService->toggleAttendance(
                user: $request->user(),
                eventId: $id,
                newStatus: (string) $request->input('status')
            );

            $msg = match ($result['status']) {
                'attending' => 'Etkinliğe katılımınız kaydedildi! 2 saat önce hatırlatma göndereceğiz. ⏰',
                'interested' => 'Etkinlik ilginizi çekti, takip listenize eklendi. 👀',
                default => 'Katılımınız geri çekildi.',
            };

            return response()->json([
                'success' => true,
                'message' => $msg,
                'attendance' => $result,
            ]);
        } catch (DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}

