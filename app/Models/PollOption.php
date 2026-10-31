<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll_id',
        'option_text',
        'votes_count',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'votes_count' => 'integer',
        ];
    }

    /**
     * Seçeneğin ait olduğu anket.
     *
     * @return BelongsTo<Poll, PollOption>
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * Bu seçeneğe verilen oylar.
     *
     * @return HasMany<PollVote>
     */
    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }
}

