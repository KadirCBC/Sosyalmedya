<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'location_id',
        'title',
        'description',
        'category',
        'venue_name',
        'address',
        'event_date',
        'max_participants',
        'participants_count',
        'status',
        'business_profile_id',
        'is_sponsored_venue',
        'reminder_sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'is_sponsored_venue' => 'boolean',
            'max_participants' => 'integer',
            'participants_count' => 'integer',
        ];
    }

    /**
     * Etkinliği oluşturan kullanıcı / mahalle sakini.
     *
     * @return BelongsTo<User, Event>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Etkinliğin gerçekleşeceği ilçe lokasyonu.
     *
     * @return BelongsTo<Location, Event>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Sponsorlu buluşma noktası olan esnaf işletmesi (varsa).
     *
     * @return BelongsTo<BusinessProfile, Event>
     */
    public function businessProfile(): BelongsTo
    {
        return $this->belongsTo(BusinessProfile::class);
    }

    /**
     * Katılımcı kayıtları.
     *
     * @return HasMany<EventAttendee>
     */
    public function attendees(): HasMany
    {
        return $this->hasMany(EventAttendee::class);
    }

    /**
     * Kesin katılan kullanıcılar ('attending').
     *
     * @return BelongsToMany<User>
     */
    public function confirmedAttendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_attendees')
            ->wherePivot('status', 'attending')
            ->withTimestamps();
    }

    /**
     * Kontenjanın dolup dolmadığını kontrol eder.
     */
    public function isFull(): bool
    {
        if ($this->max_participants === null || $this->max_participants <= 0) {
            return false;
        }

        return $this->participants_count >= $this->max_participants;
    }

    /**
     * Etkinlik zamanının geçip geçmediğini kontrol eder.
     */
    public function isPast(): bool
    {
        return now()->greaterThan($this->event_date);
    }

    /**
     * Kategoriye özel görsel etiket ve ikon bilgisi döner.
     *
     * @return array{name: string, icon: string, bg: string, text: string}
     */
    public function getCategoryMeta(): array
    {
        return match ($this->category) {
            'spor' => [
                'name' => 'Spor & Yürüyüş',
                'icon' => '⚽',
                'bg' => 'bg-emerald-100 dark:bg-emerald-950/70',
                'text' => 'text-emerald-800 dark:text-emerald-300',
            ],
            'kultur_sanat' => [
                'name' => 'Kültür & Sanat',
                'icon' => '🎨',
                'bg' => 'bg-purple-100 dark:bg-purple-950/70',
                'text' => 'text-purple-800 dark:text-purple-300',
            ],
            'yardimlasma' => [
                'name' => 'Sosyal Yardımlaşma',
                'icon' => '🤝',
                'bg' => 'bg-rose-100 dark:bg-rose-950/70',
                'text' => 'text-rose-800 dark:text-rose-300',
            ],
            'belediye_duyuru' => [
                'name' => 'Belediye / Resmi Etkinlik',
                'icon' => '🏛️',
                'bg' => 'bg-blue-100 dark:bg-blue-950/70',
                'text' => 'text-blue-800 dark:text-blue-300',
            ],
            default => [
                'name' => 'Hobi & Buluşma',
                'icon' => '☕',
                'bg' => 'bg-amber-100 dark:bg-amber-950/70',
                'text' => 'text-amber-800 dark:text-amber-300',
            ],
        };
    }
}

