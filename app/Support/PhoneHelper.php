<?php

declare(strict_types=1);

namespace App\Support;

class PhoneHelper
{
    /**
     * Türkiye telefon numarasını standart kanonik formata (905XXXXXXXXX) dönüştürür.
     *
     * Örnek girişler:
     * - "0532 123 45 67"   -> "905321234567"
     * - "+905321234567"    -> "905321234567"
     * - "5321234567"       -> "905321234567"
     */
    public static function normalize(string $phone): string
    {
        // Yalnızca rakamları al
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        // 90 ile başlıyorsa ve 12 haneli ise
        if (str_starts_with($digits, '90') && strlen($digits) === 12) {
            return $digits;
        }

        // 0 ile başlıyorsa ve 11 haneli ise (05XXXXXXXXX)
        if (str_starts_with($digits, '0') && strlen($digits) === 11) {
            return '90' . substr($digits, 1);
        }

        // 10 haneli ve 5 ile başlıyorsa (5XXXXXXXXX)
        if (str_starts_with($digits, '5') && strlen($digits) === 10) {
            return '90' . $digits;
        }

        return $digits;
    }

    /**
     * Numaranın geçerli bir Türkiye cep telefonu olup olmadığını doğrular.
     */
    public static function isValid(string $phone): bool
    {
        $normalized = self::normalize($phone);

        return (bool) preg_match('/^905[0-9]{9}$/', $normalized);
    }
}

