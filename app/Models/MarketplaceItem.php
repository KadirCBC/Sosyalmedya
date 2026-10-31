<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketplaceItem extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'location_id',
        'title',
        'description',
        'price',
        'category',
        'images',
        'status',
        'is_free',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'float',
            'images' => 'array',
            'is_free' => 'boolean',
        ];
    }

    /**
     * İlanı veren kullanıcı.
     *
     * @return BelongsTo<User, MarketplaceItem>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * İlanın ait olduğu ilçe / konum.
     *
     * @return BelongsTo<Location, MarketplaceItem>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * İlana bağlı mesajlaşma sohbetleri.
     *
     * @return HasMany<Conversation>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'item_id');
    }

    /**
     * Fiyatın biçimlendirilmiş gösterimi (Ücretsiz veya TL).
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->is_free || $this->price <= 0) {
            return '🎁 Ücretsiz / Askıda';
        }

        return number_format($this->price, 0, ',', '.') . ' TL';
    }

    /**
     * Türkçe kategori adı.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'elektronik' => '📱 Elektronik',
            'ev_esyasi' => '🛋️ Ev Eşyası',
            'giyim' => '👕 Giyim & Moda',
            'kitap_hobi' => '📚 Kitap & Hobi',
            default => '📦 Diğer',
        };
    }

    /**
     * İlan durumu etiketi.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'reserved' => 'Rezerve Edildi',
            'sold' => 'Satıldı',
            default => 'Yayında',
        };
    }
}

