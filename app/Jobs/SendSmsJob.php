<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\SmsServiceInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * İşin en fazla kaç kez deneneceği.
     */
    public int $tries = 3;

    /**
     * Başarısız olursa yeniden deneme aralıkları (saniye cinsinden).
     *
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    /**
     * @param string $phone Standart biçimli telefon numarası
     * @param string $message Gönderilecek SMS metni
     */
    public function __construct(
        public string $phone,
        public string $message
    ) {}

    /**
     * İşi yürütür.
     */
    public function handle(SmsServiceInterface $smsService): void
    {
        $sent = $smsService->send($this->phone, $this->message);

        if (! $sent) {
            throw new \RuntimeException("SMS gönderimi başarısız oldu. Numara: {$this->phone}");
        }
    }

    /**
     * İş kalıcı olarak başarısız olduğunda çalışır.
     */
    public function failed(?Throwable $exception): void
    {
        Log::critical('SendSmsJob kalıcı olarak başarısız oldu', [
            'phone' => $this->phone,
            'error' => $exception?->getMessage(),
        ]);
    }
}

