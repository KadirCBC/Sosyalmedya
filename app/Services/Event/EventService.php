<?php

declare(strict_types=1);

namespace App\Services\Event;

use App\Models\BusinessProfile;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\User;
use App\Notifications\EventReminderNotification;
use App\Services\Reputation\ReputationService;
use Carbon\Carbon;
use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventService
{
    public function __construct(
        protected ReputationService $reputationService
    ) {}

    /**
     * Yeni bir yerel etkinlik ve buluşma oluşturur.
     *
     * @param array<string, mixed> $data
     */
    public function createEvent(User $user, array $data): Event
    {
        return DB::transaction(function () use ($user, $data) {
            $locationId = (int) ($data['location_id'] ?? $user->location_id);
            $eventDate = Carbon::parse($data['event_date']);

            if ($eventDate->isPast()) {
                throw new DomainException('Etkinlik tarihi geçmiş bir zaman olamaz.');
            }

            // Esnaf Sponsorluk Kontrolü:
            // İşletme sahibi kendi mekanında etkinlik açıyorsa veya onaylı esnaf mekanı seçildiyse sponsorlu işaretle
            $businessProfileId = ! empty($data['business_profile_id']) ? (int) $data['business_profile_id'] : null;
            $isSponsored = false;

            if ($businessProfileId) {
                /** @var BusinessProfile|null $biz */
                $biz = BusinessProfile::query()->find($businessProfileId);
                if ($biz && $biz->is_verified) {
                    $isSponsored = true;
                    // Mekan adı ve adresi esnaf profilinden otomatik doldurulabilir
                    if (empty($data['venue_name'])) {
                        $data['venue_name'] = $biz->business_name;
                    }
                    if (empty($data['address'])) {
                        $data['address'] = $biz->address;
                    }
                }
            } elseif ($user->isVerifiedMerchant() && $user->businessProfile) {
                $businessProfileId = $user->businessProfile->id;
                $isSponsored = true;
                if (empty($data['venue_name'])) {
                    $data['venue_name'] = $user->businessProfile->business_name;
                }
                if (empty($data['address'])) {
                    $data['address'] = $user->businessProfile->address;
                }
            }

            /** @var Event $event */
            $event = Event::query()->create([
                'user_id' => $user->id,
                'location_id' => $locationId,
                'title' => trim((string) $data['title']),
                'description' => trim((string) $data['description']),
                'category' => (string) ($data['category'] ?? 'hobi_oyun'),
                'venue_name' => trim((string) ($data['venue_name'] ?? 'Mahalle Meydanı')),
                'address' => trim((string) ($data['address'] ?? 'Belirtilen Mekan')),
                'event_date' => $eventDate,
                'max_participants' => ! empty($data['max_participants']) ? (int) $data['max_participants'] : null,
                'participants_count' => 1, // Oluşturan kişi otomatik katılır
                'status' => 'active',
                'business_profile_id' => $businessProfileId,
                'is_sponsored_venue' => $isSponsored,
            ]);

            // Oluşturan kişiyi otomatik 'attending' olarak ekle
            EventAttendee::query()->create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'status' => 'attending',
            ]);

            // Hafta sonu önbelleğini temizle
            Cache::forget("events:weekend:{$locationId}");

            // Yerel İtibar: Mahallede sosyalleşme/etkinlik organize etme ödülü (+5 Puan)
            $this->reputationService->adjustPoints(
                userId: $user->id,
                action: 'event_created',
                pointsDelta: 5,
                description: "Mahallede '{$event->title}' buluşmasını organize etti"
            );

            return $event->load(['user', 'location', 'businessProfile']);
        });
    }

    /**
     * Kullanıcının etkinliğe katılım durumunu günceller ('attending', 'interested' veya iptal).
     *
     * @return array{status: string|null, participants_count: int, is_attending: bool, is_interested: bool}
     */
    public function toggleAttendance(User $user, int $eventId, string $newStatus = 'attending'): array
    {
        return DB::transaction(function () use ($user, $eventId, $newStatus) {
            /** @var Event $event */
            $event = Event::query()->lockForUpdate()->findOrFail($eventId);

            if ($event->isPast() || $event->status !== 'active') {
                throw new DomainException('Geçmiş veya iptal edilmiş bir etkinliğe katılım yapılamaz.');
            }

            /** @var EventAttendee|null $existing */
            $existing = EventAttendee::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            $finalStatus = null;

            if ($existing) {
                if ($existing->status === $newStatus) {
                    // Aynı duruma tekrar basıldıysa katılımı geri çek (iptal)
                    if ($existing->status === 'attending' && $event->participants_count > 0) {
                        $event->decrement('participants_count');
                    }
                    $existing->delete();
                    $finalStatus = null;
                } else {
                    // Durumu değiştir (Örn: interested -> attending)
                    if ($newStatus === 'attending') {
                        if ($event->isFull()) {
                            throw new DomainException('Bu etkinliğin katılımcı kontenjanı dolmuştur.');
                        }
                        $event->increment('participants_count');
                    } elseif ($existing->status === 'attending' && $event->participants_count > 0) {
                        $event->decrement('participants_count');
                    }
                    $existing->update(['status' => $newStatus]);
                    $finalStatus = $newStatus;
                }
            } else {
                // Yeni katılım
                if ($newStatus === 'attending') {
                    if ($event->isFull()) {
                        throw new DomainException('Bu etkinliğin katılımcı kontenjanı dolmuştur.');
                    }
                    $event->increment('participants_count');
                }
                EventAttendee::query()->create([
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'status' => $newStatus,
                ]);
                $finalStatus = $newStatus;
            }

            $event->refresh();

            return [
                'status' => $finalStatus,
                'participants_count' => (int) $event->participants_count,
                'is_attending' => $finalStatus === 'attending',
                'is_interested' => $finalStatus === 'interested',
            ];
        });
    }

    /**
     * "Bu Hafta Sonu Mahallede Ne Var?" etkinlik widget'ı için yaklaşan hafta sonu etkinliklerini çeker.
     * 10 dakika önbellekli çalışır.
     *
     * @return Collection<int, Event>
     */
    public function getWeekendEvents(int $locationId, int $limit = 5): Collection
    {
        return Cache::remember("events:weekend:{$locationId}", 600, function () use ($locationId, $limit) {
            $now = Carbon::now();

            // Eğer şu an Cuma, Cumartesi veya Pazar ise bu hafta sonunu baz al, değilse gelecek Cuma-Pazar
            if ($now->isFriday() || $now->isSaturday() || $now->isSunday()) {
                $weekendStart = $now->copy()->startOfDay();
                $weekendEnd = $now->copy()->endOfWeek()->endOfDay(); // Pazar 23:59:59
            } else {
                $weekendStart = $now->copy()->next(Carbon::FRIDAY)->startOfDay();
                $weekendEnd = $now->copy()->next(Carbon::SUNDAY)->endOfDay();
            }

            return Event::query()
                ->where('location_id', $locationId)
                ->where('status', 'active')
                ->whereBetween('event_date', [$weekendStart, $weekendEnd])
                ->orderByDesc('is_sponsored_venue') // Sponsorlu kafeler ve buluşma noktaları en üstte
                ->orderBy('event_date', 'asc')
                ->with(['businessProfile:id,business_name,logo_url,address,is_verified', 'user:id,name,username,avatar_path'])
                ->take($limit)
                ->get();
        });
    }

    /**
     * İlçedeki tüm aktif ve gelecek etkinlikleri sayfalar.
     *
     * @return LengthAwarePaginator<Event>
     */
    public function getUpcomingEvents(int $locationId, ?string $category = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Event::query()
            ->where('location_id', $locationId)
            ->where('status', 'active')
            ->where('event_date', '>=', now())
            ->orderByDesc('is_sponsored_venue')
            ->orderBy('event_date', 'asc')
            ->with(['businessProfile', 'user', 'location']);

        if ($category && in_array($category, ['spor', 'kultur_sanat', 'yardimlasma', 'hobi_oyun', 'belediye_duyuru'], true)) {
            $query->where('category', $category);
        }

        return $query->paginate($perPage);
    }

    /**
     * Etkinliğine 2 saat kalan katılımcılara otomatik bildirim ve WebPush fırlatır.
     * Konsol komutu (Cron) tarafından çağrılır.
     */
    public function sendUpcomingEventReminders(): int
    {
        $twoHoursLater = now()->addHours(2);
        $windowStart = now()->addMinutes(100); // 1 saat 40 dk
        $windowEnd = now()->addMinutes(140);   // 2 saat 20 dk

        /** @var Collection<int, Event> $upcomingEvents */
        $upcomingEvents = Event::query()
            ->where('status', 'active')
            ->whereNull('reminder_sent_at')
            ->whereBetween('event_date', [$windowStart, $windowEnd])
            ->with(['attendees' => fn ($q) => $q->where('status', 'attending')->with('user')])
            ->get();

        $notifiedCount = 0;

        foreach ($upcomingEvents as $event) {
            foreach ($event->attendees as $attendee) {
                if ($attendee->user) {
                    $attendee->user->notify(new EventReminderNotification($event));
                    $notifiedCount++;
                }
            }

            $event->update(['reminder_sent_at' => now()]);
        }

        return $notifiedCount;
    }
}

