<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Event\EventService;
use Illuminate\Console\Command;

class SendEventRemindersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Başlamasına 2 saat kalan yerel etkinlik katılımcılarına Database ve WebPush hatırlatması gönderir.';

    /**
     * Execute the console command.
     */
    public function handle(EventService $eventService): int
    {
        $this->info('Etkinlik hatırlatma bildirimleri taranıyor...');

        $count = $eventService->sendUpcomingEventReminders();

        $this->info("Toplam {$count} mahalle sakinine etkinlik hatırlatma bildirimi gönderildi.");

        return Command::SUCCESS;
    }
}

