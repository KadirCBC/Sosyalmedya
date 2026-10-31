<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Marketplace\ChatService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected ChatService $chatService,
        protected MediaService $mediaService
    ) {}

    /**
     * Kullanıcının aktif sohbetlerini listeler.
     */
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();
        $conversations = $this->chatService->getUserConversations($user);

        $data = $conversations->map(function (Conversation $c) use ($user) {
            $otherParticipant = $c->getOtherParticipant($user->id);
            $lastMessage = $c->messages->first();

            $itemImage = null;
            if ($c->item && ! empty($c->item->images) && is_array($c->item->images)) {
                $itemImage = $this->mediaService->getUrl((string) $c->item->images[0]);
            }

            return [
                'id' => $c->id,
                'last_message_at' => $c->last_message_at?->diffForHumans(),
                'item' => $c->item ? [
                    'id' => $c->item->id,
                    'title' => $c->item->title,
                    'formatted_price' => $c->item->formatted_price,
                    'status' => $c->item->status,
                    'image_url' => $itemImage,
                ] : null,
                'other_user' => [
                    'id' => $otherParticipant->id,
                    'name' => $otherParticipant->name,
                    'username' => $otherParticipant->username,
                    'avatar_url' => $this->mediaService->getUrl($otherParticipant->avatar_path),
                ],
                'last_message' => $lastMessage ? [
                    'content' => $lastMessage->content,
                    'is_mine' => $lastMessage->sender_id === $user->id,
                    'is_read' => $lastMessage->is_read,
                    'time' => $lastMessage->created_at?->diffForHumans(),
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'conversations' => $data,
        ]);
    }

    /**
     * Bir ilan üzerinden sohbet başlatır.
     */
    public function start(Request $request, int $itemId): JsonResponse
    {
        $user = $request->user();
        $conversation = $this->chatService->startOrGetConversation($user, $itemId);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'seller' => [
                'id' => $conversation->seller->id,
                'name' => $conversation->seller->name,
                'username' => $conversation->seller->username,
            ],
            'item' => [
                'id' => $conversation->item?->id,
                'title' => $conversation->item?->title,
                'price' => $conversation->item?->formatted_price,
            ],
        ]);
    }

    /**
     * Belirli bir sohbetteki mesajları getirir.
     */
    public function messages(Request $request, int $conversationId): JsonResponse
    {
        $user = $request->user();
        $messages = $this->chatService->getConversationMessages($user, $conversationId);

        $data = $messages->map(function (Message $m) use ($user) {
            return [
                'id' => $m->id,
                'content' => $m->content,
                'is_mine' => $m->sender_id === $user->id,
                'sender_name' => $m->sender?->name,
                'sender_avatar' => $this->mediaService->getUrl($m->sender?->avatar_path),
                'created_at_human' => $m->created_at?->format('H:i'),
                'is_read' => $m->is_read,
            ];
        });

        return response()->json([
            'success' => true,
            'messages' => $data,
        ]);
    }

    /**
     * Sohbete yeni mesaj yazar.
     */
    public function sendMessage(Request $request, int $conversationId): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $user = $request->user();
        $message = $this->chatService->sendMessage($user, $conversationId, $validated['content']);

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'content' => $message->content,
                'is_mine' => true,
                'sender_name' => $user->name,
                'created_at_human' => $message->created_at?->format('H:i'),
            ],
        ], 201);
    }
}

