<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostScope;
use App\Models\Scopes\NotBlockedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ScopedBy([NotBlockedScope::class])]
class Post extends Model
{
    use HasFactory;

    /**
     * Counter-cache alanları (likes_count, comments_count) mass-assignment
     * saldırılarını önlemek amacıyla fillable listesine eklenmez.
     * Bu sayaçlar yalnızca Observer/Event veya DB::raw increment/decrement ile güncellenir.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'content',
        'media_paths',
        'scope',
        'location_id',
    ];

    /**
     * Doğrudan manipülasyondan korunan alanlar.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
        'likes_count',
        'comments_count',
        'created_at',
        'updated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'media_paths' => 'array',
            'scope' => PostScope::class,
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'is_hidden' => 'boolean',
        ];
    }

    /**
     * Gönderi sahibi.
     *
     * @return BelongsTo<User, Post>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Yerel gönderinin ait olduğu lokasyon.
     *
     * @return BelongsTo<Location, Post>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Gönderiye bağlı anket (varsa).
     *
     * @return HasOne<Poll>
     */
    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class);
    }

    /**
     * Gönderiye ait beğeni kayıtları.
     *
     * @return HasMany<Like>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Gönderiye ait tüm yorumlar.
     *
     * @return HasMany<Comment>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Gönderiyi beğenen kullanıcılar.
     *
     * @return BelongsToMany<User>
     */
    public function likers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'likes')
            ->withTimestamps();
    }

    /**
     * Yerel akış sorgusu (idx_local_feed indeksini tetikler).
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeLocalFeed(Builder $query, int $locationId): Builder
    {
        return $query->where('scope', PostScope::LOCAL)
            ->where('location_id', $locationId)
            ->orderByDesc('id');
    }

    /**
     * Genel akış sorgusu (idx_general_feed indeksini tetikler).
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeGeneralFeed(Builder $query): Builder
    {
        return $query->where('scope', PostScope::GENERAL)
            ->orderByDesc('id');
    }
}

