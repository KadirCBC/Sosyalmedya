<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'poll_id',
        'poll_option_id',
        'user_id',
    ];

    /**
     * @return BelongsTo<Poll, PollVote>
     */
    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    /**
     * @return BelongsTo<PollOption, PollVote>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(PollOption::class, 'poll_option_id');
    }

    /**
     * @return BelongsTo<User, PollVote>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

