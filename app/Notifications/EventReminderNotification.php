<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Event;
use App\Models\User;
use App\Services\Push\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $timeStr = $this->event->event_date->format('H:i');
        $message = "Hatırlatma: '{$this->event->title}' etkinliği bugün saat {$timeStr}'de {$this->event->venue_name} mekanında başlıyor!";

        // WebPush kilit ekranı bildirimi fırlat
        if ($notifiable instanceof User) {
            app(WebPushService::class)->notifyUser(
                user: $notifiable,
                title: '📍 Mahalle Buluşması Yaklaşıyor!',
                body: $message,
                url: url("/events/{$this->event->id}")
            );
        }

        return [
            'type' => 'event_reminder',
            'event_id' => $this->event->id,
            'title' => $this->event->title,
            'venue_name' => $this->event->venue_name,
            'event_date' => $this->event->event_date->toISOString(),
            'message' => $message,
            'url' => url("/events/{$this->event->id}"),
        ];
    }

    /**
     * @return BroadcastMessage
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->toDatabase($notifiable),
        ]);
    }
}

