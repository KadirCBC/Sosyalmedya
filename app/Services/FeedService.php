<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PostScope;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Facades\Cache;

class FeedService
{
    /**
     * İlk sayfa yerel akış önbellek süresi (saniye).
     */
    protected const CACHE_TTL_SECONDS = 30;

    /**
     * Yerel Akış (Local Feed):
     * Yalnızca belirtilen lokasyona ait ve scope = 'local' olan gönderileri getirir.
     * İlk sayfa (cursor === null) için 30 saniyelik Cache-Aside önbellekleme katmanı uygular.
     *
     * @return CursorPaginator<Post>
     */
    public function getLocalFeed(int $locationId, ?string $cursor = null, ?int $authUserId = null): CursorPaginator
    {
        // Yalnızca ilk sayfa (cursor null iken) önbelleğe alınır
        if ($cursor === null) {
            $cacheKey = $this->getLocalFeedCacheKey($locationId, $authUserId);

            return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($locationId, $authUserId) {
                return $this->buildLocalFeedQuery($locationId, null, $authUserId);
            });
        }

        // Derin sayfalar (cursor paginated) doğrudan indeks üzerinden O(1) taranır
        return $this->buildLocalFeedQuery($locationId, $cursor, $authUserId);
    }

    /**
     * Yerel akış veritabanı sorgusu.
     * idx_local_feed (scope, location_id, id DESC) bileşik indeksini tam eşleşmeyle kullanır.
     *
     * @return CursorPaginator<Post>
     */
    protected function buildLocalFeedQuery(int $locationId, ?string $cursor = null, ?int $authUserId = null): CursorPaginator
    {
        $query = Post::query()
            ->where('scope', PostScope::LOCAL)
            ->where('location_id', $locationId)
            ->orderByDesc('id')
            ->with([
                'user:id,name,username,avatar_path',
                'location:id,city,district',
            ]);

        if ($authUserId !== null) {
            $query->withExists([
                'likes as is_liked' => fn ($q) => $q->where('user_id', $authUserId),
            ]);
        }

        return $query->cursorPaginate(
            perPage: 20,
            cursor: $cursor
        );
    }

    /**
     * Yerel akış önbellek anahtarını üretir.
     */
    public function getLocalFeedCacheKey(int $locationId, ?int $authUserId = null): string
    {
        $suffix = $authUserId !== null ? "user:{$authUserId}" : 'guest';

        return "feed:local:{$locationId}:{$suffix}:page:1";
    }

    /**
     * İlgili lokasyondaki yerel akış önbelleğini temizler.
     */
    public function clearLocalFeedCache(int $locationId): void
    {
        // Genel misafir önbelleğini temizle
        Cache::forget("feed:local:{$locationId}:guest:page:1");
    }

    /**
     * Genel Akış (General Feed):
     * Yalnızca scope = 'general' olan tüm gönderileri getirir.
     * idx_general_feed (scope, id DESC) bileşik indeksini kullanır.
     *
     * @return CursorPaginator<Post>
     */
    public function getGeneralFeed(?string $cursor = null, ?int $authUserId = null): CursorPaginator
    {
        $query = Post::query()
            ->where('scope', PostScope::GENERAL)
            ->orderByDesc('id')
            ->with([
                'user:id,name,username,avatar_path',
                'location:id,city,district',
            ]);

        if ($authUserId !== null) {
            $query->withExists([
                'likes as is_liked' => fn ($q) => $q->where('user_id', $authUserId),
            ]);
        }

        return $query->cursorPaginate(
            perPage: 20,
            cursor: $cursor
        );
    }

    /**
     * Takip Edilenler Akışı (Following Feed):
     *
     * @return CursorPaginator<Post>
     */
    public function getFollowingFeed(User $user, ?string $cursor = null): CursorPaginator
    {
        $followingIdsQuery = Follow::query()
            ->where('follower_id', $user->id)
            ->select('following_id');

        return Post::query()
            ->whereIn('user_id', $followingIdsQuery)
            ->orderByDesc('id')
            ->with([
                'user:id,name,username,avatar_path',
                'location:id,city,district',
            ])
            ->withExists([
                'likes as is_liked' => fn ($q) => $q->where('user_id', $user->id),
            ])
            ->cursorPaginate(
                perPage: 20,
                cursor: $cursor
            );
    }
}
