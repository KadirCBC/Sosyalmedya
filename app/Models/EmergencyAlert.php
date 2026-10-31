<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyAlert extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'location_id',
        'alert_type',
        'title',
        'description',
        'contact_phone',
        'latitude',
        'longitude',
        'is_verified',
        'status',
        'verified_at',
        'verified_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Uyarıyı açan kullanıcı.
     *
     * @return BelongsTo<User, EmergencyAlert>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Uyarının geçerli olduğu ilçe / konum.
     *
     * @return BelongsTo<Location, EmergencyAlert>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Uyarıyı doğrulayan moderatör / yönetici.
     *
     * @return BelongsTo<User, EmergencyAlert>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Acil durum türü simgesi.
     */
    public function getIconAttribute(): string
    {
        return match ($this->alert_type) {
            'afet_yardim' => '🚨',
            'kan_bagisi' => '🩸',
            'kayip_ilan' => '🔍',
            default => '⚠️',
        };
    }

    /**
     * Acil durum Türkçe başlığı.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->alert_type) {
            'afet_yardim' => 'Afet & Acil Yardım',
            'kan_bagisi' => 'Acil Kan İhtiyacı',
            'kayip_ilan' => 'Kayıp İlanı',
            default => 'Güvenlik & Kriz Uyarısı',
        };
    }

    /**
     * Bildirim ve banner renk şeması.
     *
     * @return array{bg: string, border: string, text: string, pulse: string}
     */
    public function getThemeClassesAttribute(): array
    {
        return match ($this->alert_type) {
            'kan_bagisi' => [
                'bg' => 'bg-red-600',
                'border' => 'border-red-700',
                'text' => 'text-white',
                'pulse' => 'bg-red-400',
            ],
            'afet_yardim' => [
                'bg' => 'bg-rose-700',
                'border' => 'border-rose-800',
                'text' => 'text-white',
                'pulse' => 'bg-rose-400',
            ],
            'kayip_ilan' => [
                'bg' => 'bg-amber-600',
                'border' => 'border-amber-700',
                'text' => 'text-white',
                'pulse' => 'bg-amber-300',
            ],
            default => [
                'bg' => 'bg-orange-600',
                'border' => 'border-orange-700',
                'text' => 'text-white',
                'pulse' => 'bg-orange-300',
            ],
        };
    }
}

