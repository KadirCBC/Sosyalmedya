<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Services\FeedService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedController extends Controller
{
    public function __construct(
        protected FeedService $feedService
    ) {}

    /**
     * Yerel Akış (Local Feed):
     * Kullanıcının bulunduğu veya parametre olarak gelen lokasyondaki gönderileri listeler.
     */
    public function local(Request $request): AnonymousResourceCollection
    {
        $user = $request->user('sanctum');
        $locationId = (int) ($request->query('location_id') ?? $user?->location_id);
        $cursor = $request->query('cursor');

        $posts = $this->feedService->getLocalFeed(
            locationId: $locationId,
            cursor: is_string($cursor) ? $cursor : null,
            authUserId: $user?->id
        );

        return PostResource::collection($posts);
    }

    /**
     * Genel Akış (General Feed):
     * Tüm platformdaki 'general' kapsamındaki gönderileri listeler.
     */
    public function general(Request $request): AnonymousResourceCollection
    {
        $cursor = $request->query('cursor');
        $user = $request->user('sanctum');

        $posts = $this->feedService->getGeneralFeed(
            cursor: is_string($cursor) ? $cursor : null,
            authUserId: $user?->id
        );

        return PostResource::collection($posts);
    }

    /**
     * Takip Edilenler Akışı (Following Feed):
     * Giriş yapmış kullanıcının takip ettiği hesapların gönderilerini listeler.
     */
    public function following(Request $request): AnonymousResourceCollection
    {
        $cursor = $request->query('cursor');
        $user = $request->user();

        $posts = $this->feedService->getFollowingFeed(
            user: $user,
            cursor: is_string($cursor) ? $cursor : null
        );

        return PostResource::collection($posts);
    }
}
