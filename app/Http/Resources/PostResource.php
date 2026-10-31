<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\PostScope;
use App\Models\Post;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 */
class PostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var MediaService $mediaService */
        $mediaService = app(MediaService::class);

        // Medya yollarını tam Cloudflare R2 / CDN URL adreslerine dönüştür
        $mediaUrls = [];
        if (! empty($this->media_paths) && is_array($this->media_paths)) {
            foreach ($this->media_paths as $path) {
                $mediaUrls[] = $mediaService->getUrl((string) $path);
            }
        }

        // Giriş yapan kullanıcının bu gönderiyi beğenip beğenmediğini tespit et
        $isLiked = false;
        if (isset($this->is_liked)) {
            $isLiked = (bool) $this->is_liked;
        } elseif ($request->user('sanctum')) {
            $isLiked = $this->likes()->where('user_id', $request->user('sanctum')->id)->exists();
        }

        return [
            'id' => $this->id,
            'content' => $this->content,
            'scope' => $this->scope instanceof PostScope ? $this->scope->value : $this->scope,
            'likes_count' => (int) $this->likes_count,
            'comments_count' => (int) $this->comments_count,
            'media_urls' => $mediaUrls,
            'is_liked' => $isLiked,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'username' => $this->user?->username,
                'avatar_url' => $mediaService->getUrl($this->user?->avatar_path),
                'is_verified_merchant' => (bool) ($this->user?->isVerifiedMerchant() ?? false),
                'business_profile_id' => $this->user?->businessProfile?->id,
                'reputation' => [
                    'points' => (int) ($this->user?->reputation?->points ?? 0),
                    'level' => ($this->user?->isVerifiedMerchant() ?? false) ? 'Mahalle Esnafı' : ($this->user?->reputation?->level ?? 'Yeni Komşu'),
                    'badge_icon' => ($this->user?->isVerifiedMerchant() ?? false) ? '🏪' : ($this->user?->reputation?->badge_icon ?? '🌱'),
                    'badges' => $this->user?->reputation?->badges ?? ['🌱 Yeni Komşu'],
                ],
            ],
            'location' => $this->when($this->location !== null, fn () => [
                'id' => $this->location->id,
                'city' => $this->location->city,
                'district' => $this->location->district,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'created_at_human' => $this->created_at?->diffForHumans(),
        ];
    }
}

