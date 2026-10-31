<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use App\Services\MediaService;
use App\Services\Push\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class PostInteractedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param User $causer Eylemi gerçekleştiren kullanıcı (beğenen veya yorum yapan)
     * @param Post $post İlgili gönderi
     * @param string $interactionType 'like' veya 'comment'
     * @param string|null $commentContent Yorum metni (eğer etkileşim yorum ise)
     */
    public function __construct(
        public User $causer,
        public Post $post,
        public string $interactionType,
        public ?string $commentContent = null
    ) {}

    /**
     * Bildirim kanallarını belirler.
     * Kullanıcı kendi gönderisiyle etkileşime geçtiğinde boş dizi döner (bildirim engellenir).
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        // Kendi gönderisini beğenen veya yorum yapan kullanıcıya bildirim gitmez
        if ($this->causer->id === $notifiable->id) {
            return [];
        }

        return ['database', 'broadcast'];
    }

    /**
     * Veritabanı bildirimi verisi ve WebPush kilit ekranı tetiklemesi.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $data = $this->formatNotificationData();

        // Kullanıcının kayıtlı telefon veya masaüstü WebPush cihazlarına anlık bildirim fırlat
        if ($notifiable instanceof User) {
            app(WebPushService::class)->notifyUser(
                user: $notifiable,
                title: 'Meydan Bildirimi',
                body: (string) $data['message'],
                url: url("/posts/{$this->post->id}")
            );
        }

        return $data;
    }

    /**
     * Gerçek zamanlı broadcast bildirimi verisi (Reverb / Pusher / SSE).
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->formatNotificationData());
    }

    /**
     * Ortak bildirim veri yapısı.
     *
     * @return array<string, mixed>
     */
    protected function formatNotificationData(): array
    {
        /** @var MediaService $mediaService */
        $mediaService = app(MediaService::class);

        $message = match ($this->interactionType) {
            'like' => "{$this->causer->name} gönderini beğendi.",
            'comment' => "{$this->causer->name} gönderine yorum yaptı: \"" . Str::limit((string) $this->commentContent, 40) . '"',
            default => "{$this->causer->name} gönderinle etkileşime geçti.",
        };

        return [
            'type' => $this->interactionType,
            'message' => $message,
            'causer' => [
                'id' => $this->causer->id,
                'name' => $this->causer->name,
                'username' => $this->causer->username,
                'avatar_url' => $mediaService->getUrl($this->causer->avatar_path),
            ],
            'post' => [
                'id' => $this->post->id,
                'content_preview' => Str::limit($this->post->content, 60),
            ],
            'comment' => $this->commentContent !== null ? [
                'content' => $this->commentContent,
            ] : null,
        ];
    }
}

