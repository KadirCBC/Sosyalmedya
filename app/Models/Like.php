<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\LikeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([LikeObserver::class])]
class Like extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'post_id',
    ];

    /**
     * Beğeniyi yapan kullanıcı.
     *
     * @return BelongsTo<User, Like>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Beğenilen gönderi.
     *
     * @return BelongsTo<Post, Like>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
