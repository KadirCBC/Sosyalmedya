<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\SmsServiceInterface;
use App\Services\Sms\LogSmsService;
use App\Services\Sms\NetgsmSmsService;
use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(SmsServiceInterface::class, function ($app) {
            $driver = config('sms.default', 'netgsm');

            return match ($driver) {
                'log' => new LogSmsService(),
                default => new NetgsmSmsService(),
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

