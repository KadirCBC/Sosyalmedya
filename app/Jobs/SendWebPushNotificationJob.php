<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\PushSubscription;
use App\Services\Push\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWebPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 15;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public PushSubscription $subscription,
        public array $payload
    ) {}

    /**
     * İşi yürütür.
     */
    public function handle(WebPushService $webPushService): void
    {
        $webPushService->executePush($this->subscription, $this->payload);
    }
}

