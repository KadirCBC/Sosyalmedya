<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'question',
        'expires_at',
        'total_votes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'total_votes' => 'integer',
        ];
    }

    /**
     * Anketin süresinin dolup dolmadığını kontrol eder.
     */
    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    /**
     * Anketin ait olduğu gönderi.
     *
     * @return BelongsTo<Post, Poll>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Anket seçenekleri.
     *
     * @return HasMany<PollOption>
     */
    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('id');
    }

    /**
     * Ankete verilmiş tüm oylar.
     *
     * @return HasMany<PollVote>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }
}

