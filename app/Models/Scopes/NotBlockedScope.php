<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Models\Block;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class NotBlockedScope implements Scope
{
    /**
     * Engellenen veya engelleyen kullanıcıların içeriklerini ve gizlenmiş gönderileri otomatik olarak sorgudan filtreler.
     *
     * @param Builder<Model> $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        // 1. Şikayet eşiğini aşmış veya moderasyonca gizlenmiş gönderileri gizle
        if ($model->getConnection()->getSchemaBuilder()->hasColumn($model->getTable(), 'is_hidden')) {
            $builder->where($model->qualifyColumn('is_hidden'), false);
        }

        // 2. Çift yönlü engelleme filtresi (Oturum açmış kullanıcı varsa)
        $userId = auth('sanctum')->id();

        if ($userId !== null) {
            $userColumn = $model->getTable() === 'users' ? 'id' : 'user_id';

            // Kullanıcının engellediği kişiler
            $blockedUserIdsQuery = Block::query()
                ->where('user_id', $userId)
                ->select('blocked_user_id');

            // Kullanıcıyı engelleyen kişiler
            $blockerUserIdsQuery = Block::query()
                ->where('blocked_user_id', $userId)
                ->select('user_id');

            $builder->whereNotIn($model->qualifyColumn($userColumn), $blockedUserIdsQuery)
                ->whereNotIn($model->qualifyColumn($userColumn), $blockerUserIdsQuery);
        }
    }
}

