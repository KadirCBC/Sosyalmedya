<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\Event;
use App\Models\Location;
use App\Services\Event\EventService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EventWebController extends Controller
{
    public function __construct(
        protected EventService $eventService
    ) {}

    /**
     * Yerel etkinlikler takvimi ve listesi.
     */
    public function index(Request $request): View
    {
        $locationId = (int) ($request->input('location_id') ?? auth()->user()->location_id ?? 1);
        $category = $request->input('category');

        $location = Location::query()->find($locationId) ?? Location::query()->first();
        $weekendEvents = $this->eventService->getWeekendEvents($locationId, 5);
        $upcomingEvents = $this->eventService->getUpcomingEvents($locationId, $category, 12);

        return view('events.index', [
            'location' => $location,
            'weekendEvents' => $weekendEvents,
            'upcomingEvents' => $upcomingEvents,
            'activeCategory' => $category,
        ]);
    }

    /**
     * Yeni etkinlik oluşturma sayfası.
     */
    public function create(Request $request): View
    {
        $locationId = (int) (auth()->user()->location_id ?? 1);
        $location = Location::query()->find($locationId) ?? Location::query()->first();

        // İlçedeki onaylı esnaf buluşma mekanları (Kafeler, Restoranlar vb.)
        $sponsoredVenues = BusinessProfile::query()
            ->where('location_id', $locationId)
            ->where('is_verified', true)
            ->get(['id', 'business_name', 'address', 'category', 'logo_url']);

        return view('events.create', [
            'location' => $location,
            'sponsoredVenues' => $sponsoredVenues,
        ]);
    }

    /**
     * Etkinlik detay sayfası.
     */
    public function show(Request $request, int $id): View
    {
        /** @var Event $event */
        $event = Event::query()
            ->with([
                'user:id,name,username,avatar_path',
                'location:id,city,district',
                'businessProfile:id,business_name,address,phone,logo_url,is_verified,google_maps_url',
                'attendees.user:id,name,username,avatar_path',
            ])
            ->findOrFail($id);

        $currentUserAttendee = auth()->check()
            ? $event->attendees->firstWhere('user_id', auth()->id())
            : null;

        return view('events.show', [
            'event' => $event,
            'currentUserAttendee' => $currentUserAttendee,
            'categoryMeta' => $event->getCategoryMeta(),
        ]);
    }
}

