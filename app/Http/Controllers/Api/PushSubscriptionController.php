<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PushSubscriptionController extends Controller
{
    /**
     * VAPID Public Key bilgisini döner.
     */
    public function getVapidPublicKey(): JsonResponse
    {
        return response()->json([
            'public_key' => config('webpush.vapid.public_key'),
        ]);
    }

    /**
     * Kullanıcının tarayıcı WebPush aboneliğini kaydeder.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['nullable', 'string'],
            'keys.auth' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        PushSubscription::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'endpoint' => (string) $request->input('endpoint'),
            ],
            [
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
            ]
        );

        return response()->json([
            'message' => 'WebPush bildirim aboneliği başarıyla kaydedildi.',
        ], Response::HTTP_CREATED);
    }

    /**
     * Kullanıcının tarayıcı aboneliğini kaldırır.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        PushSubscription::query()
            ->where('user_id', $request->user()->id)
            ->where('endpoint', (string) $request->input('endpoint'))
            ->delete();

        return response()->json([
            'message' => 'WebPush bildirim aboneliği kaldırıldı.',
        ], Response::HTTP_OK);
    }
}

