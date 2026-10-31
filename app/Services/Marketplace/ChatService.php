<?php

declare(strict_types=1);

namespace App\Services\Marketplace;

use App\Models\Conversation;
use App\Models\MarketplaceItem;
use App\Models\Message;
use App\Models\User;
use App\Services\Moderation\ContentFilterService;
use App\Services\Push\WebPushService;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ChatService
{
    public function __construct(
        protected ContentFilterService $contentFilterService,
        protected WebPushService $webPushService
    ) {}

    /**
     * İlan üzerinden alıcı ve satıcı arasında özel sohbet başlatır veya mevcut olanı döner.
     */
    public function startOrGetConversation(User $buyer, int $itemId): Conversation
    {
        /** @var MarketplaceItem $item */
        $item = MarketplaceItem::query()->findOrFail($itemId);

        if ($buyer->id === $item->user_id) {
            throw new BadRequestHttpException('Kendi ilanınız için sohbet başlatamazsınız.');
        }

        /** @var Conversation $conversation */
        $conversation = Conversation::query()->firstOrCreate(
            [
                'item_id' => $item->id,
                'buyer_id' => $buyer->id,
                'seller_id' => $item->user_id,
            ],
            [
                'last_message_at' => now(),
            ]
        );

        return $conversation->load([
            'item:id,title,price,is_free,status,images',
            'buyer:id,name,username,avatar_path',
            'seller:id,name,username,avatar_path',
        ]);
    }

    /**
     * Sohbete yeni bir mesaj gönderir.
     */
    public function sendMessage(User $sender, int $conversationId, string $content): Message
    {
        /** @var Conversation $conversation */
        $conversation = Conversation::query()
            ->with(['item:id,title', 'buyer', 'seller'])
            ->findOrFail($conversationId);

        if ($sender->id !== $conversation->buyer_id && $sender->id !== $conversation->seller_id) {
            throw new AccessDeniedHttpException('Bu sohbete mesaj gönderme yetkiniz bulunmamaktadır.');
        }

        $cleanContent = $this->contentFilterService->inspectAndSanitize($content);

        /** @var Message $message */
        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $sender->id,
            'content' => $cleanContent,
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Karşı tarafa yerel WebPush bildirimi gönder (Kilit ekranı uyarısı)
        $receiver = $conversation->getOtherParticipant($sender->id);
        $itemTitle = $conversation->item?->title ?? 'Mahalle Pazarı';

        $this->webPushService->sendToUser(
            $receiver,
            "💬 {$sender->name} size mesaj gönderdi",
            mb_substr($cleanContent, 0, 80) . " - ({$itemTitle})",
            "/marketplace/conversations/{$conversation->id}"
        );

        return $message->load('sender:id,name,username,avatar_path');
    }

    /**
     * Sohbetteki tüm mesajları listeler ve okunmamış mesajları okundu yapar.
     *
     * @return Collection<int, Message>
     */
    public function getConversationMessages(User $user, int $conversationId): Collection
    {
        /** @var Conversation $conversation */
        $conversation = Conversation::query()->findOrFail($conversationId);

        if ($user->id !== $conversation->buyer_id && $user->id !== $conversation->seller_id) {
            throw new AccessDeniedHttpException('Bu mesajları görme yetkiniz bulunmamaktadır.');
        }

        // Karşı tarafın gönderdiği okunmamış mesajları 'okundu' olarak işaretle
        Message::query()
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return Message::query()
            ->where('conversation_id', $conversationId)
            ->orderBy('id', 'asc')
            ->with('sender:id,name,username,avatar_path')
            ->get();
    }

    /**
     * Kullanıcının dahil olduğu tüm aktif sohbetleri döner.
     *
     * @return Collection<int, Conversation>
     */
    public function getUserConversations(User $user): Collection
    {
        return Conversation::query()
            ->where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->id)
            ->orderByDesc('last_message_at')
            ->with([
                'item:id,title,price,is_free,status,images',
                'buyer:id,name,username,avatar_path',
                'seller:id,name,username,avatar_path',
                'messages' => fn ($q) => $q->latest('id')->take(1),
            ])
            ->get();
    }
}

