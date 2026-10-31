<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes & Scheduled Tasks (Laravel 11)
|--------------------------------------------------------------------------
|
| Meydan platformu otomatik haber tohumlayıcı ve yerel trend simülatörü
| zamanlanmış görev tanımları.
|
*/

// 1. Resmi Belediye Bültenleri, Su/Elektrik Kesintileri ve Spor Haberleri
// Sabah 07:30 ve Öğleden sonra 15:00'te günde iki kez çalışır
Schedule::command('seed:local-news')
    ->dailyAt('07:30')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('seed:local-news')
    ->dailyAt('15:00')
    ->withoutOverlapping()
    ->runInBackground();

// 2. İlk Gün Yerel Tartışma ve Trend Simülatörü
// Sabah kahvaltı & ulaşım konusu (08:30)
Schedule::command('simulate:local-trends --time=morning')
    ->dailyAt('08:30')
    ->withoutOverlapping()
    ->runInBackground();

// Öğle yemeği ve esnaf keşif konusu (12:30)
Schedule::command('simulate:local-trends --time=noon')
    ->dailyAt('12:30')
    ->withoutOverlapping()
    ->runInBackground();

// Akşam & Hafta sonu etkinlik konusu (19:00)
Schedule::command('simulate:local-trends --time=evening')
    ->dailyAt('19:00')
    ->withoutOverlapping()
    ->runInBackground();

// 3. Veritabanı Temizliği ve 5651 Log Bakımı
Schedule::command('queue:prune-failed --hours=168') // 7 günlük başarısız işleri temizle
    ->weekly();

