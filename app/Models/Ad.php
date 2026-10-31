<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AdStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ad extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'location_id',
        'title',
        'content',
        'image_url',
        'cta_text',
        'cta_url',
        'status',
        'budget',
        'impressions_count',
        'clicks_count',
        'starts_at',
        'ends_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AdStatus::class,
            'budget' => 'decimal:2',
            'impressions_count' => 'integer',
            'clicks_count' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * İlan sahibi (esnaf/işletme).
     *
     * @return BelongsTo<User, Ad>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * İlanın hedeflendiği ilçe (boşsa tüm platform).
     *
     * @return BelongsTo<Location, Ad>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Aktif ve yayında olan ilanlar.
     *
     * @param  Builder<Ad>  $query
     * @return Builder<Ad>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', AdStatus::ACTIVE)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Tıklanma Oranı (CTR - Click Through Rate %).
     */
    public function getCtrAttribute(): float
    {
        if ($this->impressions_count === 0) {
            return 0.0;
        }

        return round(($this->clicks_count / $this->impressions_count) * 100, 2);
    }
}

