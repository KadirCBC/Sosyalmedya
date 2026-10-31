<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Contracts\SmsServiceInterface;
use Illuminate\Support\Facades\Log;

class LogSmsService implements SmsServiceInterface
{
    /**
     * Yerel geliştirme ve test ortamlarında SMS'i log dosyasına yazar.
     */
    public function send(string $phone, string $message): bool
    {
        Log::info('Local/Mock SMS Gönderildi', [
            'phone' => $phone,
            'message' => $message,
        ]);

        return true;
    }
}

