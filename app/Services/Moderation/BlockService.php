<?php

declare(strict_types=1);

namespace App\Services\Moderation;

use App\Models\Block;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BlockService
{
    /**
     * Kullanıcıyı engeller. Karşılıklı takip ilişkilerini de sonlandırır.
     *
     * @throws ValidationException
     */
    public function blockUser(User $user, int $targetUserId): bool
    {
        if ($user->id === $targetUserId) {
            throw ValidationException::withMessages([
                'user_id' => ['Kendinizi engelleyemezsiniz.'],
            ]);
        }

        // Hedef kullanıcının varlığını doğrula
        User::query()->findOrFail($targetUserId);

        return DB::transaction(function () use ($user, $targetUserId) {
            // Engeli kaydet
            Block::query()->firstOrCreate([
                'user_id' => $user->id,
                'blocked_user_id' => $targetUserId,
            ]);

            // Karşılıklı takipleri otomatik düşür
            Follow::query()
                ->where(function ($q) use ($user, $targetUserId) {
                    $q->where('follower_id', $user->id)
                        ->where('following_id', $targetUserId);
                })
                ->orWhere(function ($q) use ($user, $targetUserId) {
                    $q->where('follower_id', $targetUserId)
                        ->where('following_id', $user->id);
                })
                ->delete();

            return true;
        });
    }

    /**
     * Kullanıcının engelini kaldırır.
     */
    public function unblockUser(User $user, int $targetUserId): bool
    {
        return (bool) Block::query()
            ->where('user_id', $user->id)
            ->where('blocked_user_id', $targetUserId)
            ->delete();
    }
}

