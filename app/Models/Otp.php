<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    /**
     * Otps tablosunda yalnızca created_at bulunmaktadır.
     */
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'phone',
        'code',
        'expires_at',
        'is_used',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'is_used' => 'boolean',
        ];
    }

    /**
     * OTP kodunun henüz süresinin dolmadığını ve kullanılmadığını kontrol eder.
     */
    public function isValid(): bool
    {
        return ! $this->is_used && $this->expires_at->isFuture();
    }

    /**
     * Geçerli ve kullanılmamış OTP arama kapsamı.
     *
     * @param  Builder<Otp>  $query
     * @return Builder<Otp>
     */
    public function scopeValid(Builder $query, string $phone, string $code): Builder
    {
        return $query->where('phone', $phone)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now());
    }
}

