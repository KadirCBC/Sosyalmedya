<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProfile extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'location_id',
        'business_name',
        'tax_number',
        'category',
        'address',
        'google_maps_url',
        'phone',
        'whatsapp_number',
        'opening_hours',
        'logo_url',
        'cover_url',
        'is_verified',
        'rating_avg',
        'reviews_count',
        'subscription_status',
        'subscription_expires_at',
        'free_boosts_remaining',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
            'is_verified' => 'boolean',
            'rating_avg' => 'float',
            'reviews_count' => 'integer',
            'subscription_expires_at' => 'datetime',
            'free_boosts_remaining' => 'integer',
        ];
    }

    /**
     * İşletme sahibi kullanıcı.
     *
     * @return BelongsTo<User, BusinessProfile>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * İşletmenin kayıtlı olduğu ilçe.
     *
     * @return BelongsTo<Location, BusinessProfile>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * İşletmenin menü / katalog ürünleri.
     *
     * @return HasMany<BusinessCatalogItem>
     */
    public function catalogItems(): HasMany
    {
        return $this->hasMany(BusinessCatalogItem::class);
    }

    /**
     * İşletmeye yapılan müşteri değerlendirmeleri.
     *
     * @return HasMany<BusinessReview>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(BusinessReview::class);
    }

    /**
     * Doğrudan WhatsApp sipariş linki üretir.
     */
    public function getWhatsappOrderUrlAttribute(): ?string
    {
        $rawNumber = $this->whatsapp_number ?: $this->phone;
        if (! $rawNumber) {
            return null;
        }

        // Sadece rakamları al
        $cleanNumber = preg_replace('/[^0-9]/', '', $rawNumber);
        if (str_starts_with($cleanNumber, '0')) {
            $cleanNumber = '90' . substr($cleanNumber, 1);
        } elseif (! str_starts_with($cleanNumber, '90')) {
            $cleanNumber = '90' . $cleanNumber;
        }

        $msg = urlencode("Merhaba {$this->business_name}, Meydan platformu üzerinden sipariş vermek ve bilgi almak istiyorum.");

        return "https://wa.me/{$cleanNumber}?text={$msg}";
    }

    /**
     * Kategori başlığı ve ikonu.
     */
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'restoran_kafe' => '🍽️ Restoran & Kafe',
            'hizmet_usta' => '🔧 Usta & Teknik Hizmet',
            'perakende_butik' => '🛍️ Butik & Mağaza',
            default => '🏪 Yerel Esnaf',
        };
    }

    /**
     * Aboneliğin aktif olup olmadığını döner.
     */
    public function isSubscriptionActive(): bool
    {
        return $this->subscription_status === 'active'
            && ($this->subscription_expires_at === null || $this->subscription_expires_at->isFuture());
    }
}

