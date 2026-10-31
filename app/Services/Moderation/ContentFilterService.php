<?php

declare(strict_types=1);

namespace App\Services\Moderation;

use Illuminate\Validation\ValidationException;

class ContentFilterService
{
    /**
     * Yasadışı bahis ve kumar anahtar kelimeleri (7258 Sayılı Kanun).
     *
     * @var list<string>
     */
    protected array $illegalGamblingKeywords = [
        'bahis',
        'yasadışı bahis',
        'kaçak bahis',
        'canlı casino',
        'rulet',
        'slot oyna',
        'tatubet',
        'sweet bonanza',
        'deneme bonusu',
        'mobilbahis',
        'vdcasino',
        'iddaa kuponu satılık',
    ];

    /**
     * Ağır hakaret, nefret söylemi ve terör propagandası filtre listesi.
     *
     * @var list<string>
     */
    protected array $severeKeywords = [
        'terör',
        'bomba yapımı',
        'suikast',
        'intihar et',
    ];

    /**
     * Maskelenecek genel argo/küfür kelimeleri.
     *
     * @var list<string>
     */
    protected array $profanities = [
        'orospu',
        'piç',
        'siktir',
        'amk',
        'aq',
        'oç',
        'yavşak',
        'götlek',
    ];

    /**
     * İçeriği mevzuata (5651, KVKK, 7258) göre denetler.
     * İhlal varsa doğrudan ValidationException fırlatır, hafif argo varsa maskeler.
     *
     * @throws ValidationException
     */
    public function inspectAndSanitize(string $content): string
    {
        // 1. KVKK Kontrolü: T.C. Kimlik No Taraması
        if ($this->containsValidTcKimlikNo($content)) {
            throw ValidationException::withMessages([
                'content' => ['İçerikte T.C. Kimlik Numarası tespit edildi. 6698 Sayılı KVKK uyarınca kişisel verilerin açık paylaşımı yasaktır.'],
            ]);
        }

        // 2. KVKK / Finansal Veri: IBAN Taraması
        if ($this->containsIban($content)) {
            throw ValidationException::withMessages([
                'content' => ['İçerikte IBAN bilgisi tespit edildi. Dolandırıcılık ve veri güvenliği gerekçesiyle paylaşılamaz.'],
            ]);
        }

        // 3. Yasadışı Bahis Taraması (7258 Sayılı Kanun)
        foreach ($this->illegalGamblingKeywords as $keyword) {
            if (mb_stripos($content, $keyword) !== false) {
                throw ValidationException::withMessages([
                    'content' => ['İçeriğiniz 7258 Sayılı Kanun (Yasadışı Bahis ve Şans Oyunları) kapsamında yasaklı ifadeler içermektedir.'],
                ]);
            }
        }

        // 4. Ağır Suç / Terör / Nefret Söylemi Taraması
        foreach ($this->severeKeywords as $severeWord) {
            if (mb_stripos($content, $severeWord) !== false) {
                throw ValidationException::withMessages([
                    'content' => ['İçeriğiniz kamu güvenliği ve platform kurallarına aykırı kritik ihlaller içermektedir.'],
                ]);
            }
        }

        // 5. Genel Küfür ve Hakaret Maskeleme
        return $this->maskProfanities($content);
    }

    /**
     * Metin içinde matematiksel algoritması doğrulanmış bir T.C. Kimlik Numarası olup olmadığını kontrol eder.
     */
    public function containsValidTcKimlikNo(string $content): bool
    {
        // 11 haneli sayıları bul
        if (preg_match_all('/\b([1-9][0-9]{10})\b/', $content, $matches)) {
            foreach ($matches[1] as $candidate) {
                if ($this->isValidTcKimlikNo($candidate)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * TCKN resmi doğrulama algoritması.
     */
    protected function isValidTcKimlikNo(string $tc): bool
    {
        if (strlen($tc) !== 11 || ! ctype_digit($tc) || $tc[0] === '0') {
            return false;
        }

        $d = array_map('intval', str_split($tc));

        // 1, 3, 5, 7, 9. basamakların toplamının 7 katından, 2, 4, 6, 8. basamakların toplamı çıkarıldığında
        // elde edilen sonucun 10'a bölümünden kalan 10. basamağı vermelidir.
        $oddSum = $d[0] + $d[2] + $d[4] + $d[6] + $d[8];
        $evenSum = $d[1] + $d[3] + $d[5] + $d[7];

        $tenthDigit = (($oddSum * 7) - $evenSum) % 10;
        if ($tenthDigit < 0) {
            $tenthDigit += 10;
        }

        if ($tenthDigit !== $d[9]) {
            return false;
        }

        // İlk 10 basamağın toplamının 10'a bölümünden kalan 11. basamağı vermelidir.
        $firstTenSum = array_sum(array_slice($d, 0, 10));

        return ($firstTenSum % 10) === $d[10];
    }

    /**
     * Metin içinde Türkiye IBAN formatı olup olmadığını denetler.
     */
    public function containsIban(string $content): bool
    {
        return (bool) preg_match('/\bTR[0-9]{2}\s?(?:[0-9]{4}\s?){5}[0-9]{2}\b/i', $content);
    }

    /**
     * Genel küfür ve argo kelimeleri yıldızlarla maskeler.
     */
    public function maskProfanities(string $content): string
    {
        foreach ($this->profanities as $word) {
            $pattern = '/\b' . preg_quote($word, '/') . '\b/iu';
            $replacement = str_repeat('*', mb_strlen($word));
            $content = (string) preg_replace($pattern, $replacement, $content);
        }

        return $content;
    }
}

