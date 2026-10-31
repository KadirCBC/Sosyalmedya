<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'buyer_id',
        'seller_id',
        'last_message_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    /**
     * İlan ilişkisi.
     *
     * @return BelongsTo<MarketplaceItem, Conversation>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(MarketplaceItem::class, 'item_id');
    }

    /**
     * Alıcı kullanıcı.
     *
     * @return BelongsTo<User, Conversation>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Satıcı kullanıcı.
     *
     * @return BelongsTo<User, Conversation>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Sohbete ait tüm mesajlar.
     *
     * @return HasMany<Message>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Oturum açan kullanıcının karşısındaki muhatabı döner.
     */
    public function getOtherParticipant(int $currentUserId): User
    {
        return (int) $this->buyer_id === $currentUserId
            ? $this->seller
            : $this->buyer;
    }
}

