<?php

declare(strict_types=1);

namespace App\Contracts;

interface SmsServiceInterface
{
    /**
     * Belirtilen telefon numarasına tekil SMS gönderir.
     *
     * @param string $phone Standart biçimlendirilmiş telefon numarası (örn: 905xxxxxxxxx)
     * @param string $message Gönderilecek SMS mesaj metni
     * @return bool Gönderim başarılı ise true, başarısız ise false
     */
    public function send(string $phone, string $message): bool;
}

