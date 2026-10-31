<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PostScope;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostInteractedNotification;
use App\Services\Audit\ActivityLogService;
use App\Services\Moderation\ContentFilterService;
use App\Services\Reputation\ReputationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class PostService
{
    public function __construct(
        protected MediaService $mediaService,
        protected FeedService $feedService,
        protected ContentFilterService $contentFilterService,
        protected ActivityLogService $activityLogService,
        protected ReputationService $reputationService
    ) {}

    /**
     * Yeni bir gönderi oluşturur, varsa görselleri WebP olarak Cloudflare R2'ye yükler.
     *
     * @param array<string, mixed> $data
     * @param list<UploadedFile> $images
     */
    public function createPost(User $user, array $data, array $images = []): Post
    {
        $scope = $data['scope'] instanceof PostScope
            ? $data['scope']
            : PostScope::from((string) $data['scope']);

        // Eğer local seçildiyse ve konum belirtilmediyse kullanıcının kayıtlı konumunu ata
        $locationId = null;
        if ($scope === PostScope::LOCAL) {
            $locationId = ! empty($data['location_id'])
                ? (int) $data['location_id']
                : (int) $user->location_id;
        } elseif (! empty($data['location_id'])) {
            $locationId = (int) $data['location_id'];
        }

        // Varsa görselleri WebP formatına çevirerek Cloudflare R2'ye yükle
        $mediaPaths = [];
        if (! empty($images)) {
            $mediaPaths = $this->mediaService->uploadMultipleImages($images, 'posts');
        }

        // Mevzuat ve Güvenlik Filtresi (TCKN, IBAN, Yasadışı Bahis, Terör, Küfür Maskeleme)
        $cleanContent = $this->contentFilterService->inspectAndSanitize((string) $data['content']);

        /** @var Post $post */
        $post = Post::query()->create([
            'user_id' => $user->id,
            'content' => $cleanContent,
            'media_paths' => ! empty($mediaPaths) ? $mediaPaths : null,
            'scope' => $scope,
            'location_id' => $locationId,
        ]);

        // 5651 Sayılı Kanun Yer Sağlayıcı Trafik ve İçerik Kaydı (IP, Port, SHA-256 Hash)
        $this->activityLogService->log(
            request: request(),
            action: 'post_create',
            user: $user,
            content: $cleanContent,
            targetType: Post::class,
            targetId: $post->id
        );

        // Yerel akış önbelleğini temizle (yeni post anında görünsün)
        if ($scope === PostScope::LOCAL && $locationId !== null) {
            $this->feedService->clearLocalFeedCache($locationId);
        }

        // Yerel İtibar: Gönderi paylaşımı puanı ekle (+2 Puan)
        $this->reputationService->awardPostCreated($user->id);

        return $post->load([
            'user:id,name,username,avatar_path',
            'user.reputation',
            'location:id,city,district',
        ]);
    }

    /**
     * Atomik transaction ile beğeni ekler veya kaldırır.
     * LikeObserver sayesinde posts.likes_count otomatik olarak artırılır / azaltılır.
     *
     * @return array{is_liked: bool, likes_count: int}
     */
    public function toggleLike(User $user, int $postId): array
    {
        return DB::transaction(function () use ($user, $postId) {
            /** @var Post $post */
            $post = Post::query()->findOrFail($postId);

            /** @var Like|null $existingLike */
            $existingLike = Like::query()
                ->where('user_id', $user->id)
                ->where('post_id', $postId)
                ->lockForUpdate()
                ->first();

            if ($existingLike) {
                // Beğeniyi kaldır (Observer likes_count değerini 1 azaltır)
                $existingLike->delete();
                $isLiked = false;
            } else {
                // Yeni beğeni oluştur (Observer likes_count değerini 1 artırır)
                Like::query()->create([
                    'user_id' => $user->id,
                    'post_id' => $postId,
                ]);
                $isLiked = true;
            }

            // Güncel sayacı veritabanından tazele
            $post->refresh();

            // Gönderi sahibi kendisi değilse ve yeni beğeni ise bildirim gönder ve itibar puanı ekle
            if ($isLiked && $post->user_id !== $user->id) {
                $post->user->notify(new PostInteractedNotification($user, $post, 'like'));

                // Eğer beğenen kişi gönderiyle aynı ilçedeyse yerel itibar puanı ver (+5 Puan)
                if ($user->location_id && ((int) $user->location_id === (int) $post->location_id || (int) $user->location_id === (int) $post->user->location_id)) {
                    $this->reputationService->awardLocalLike($post->user_id, $user->name);
                }
            }

            return [
                'is_liked' => $isLiked,
                'likes_count' => (int) $post->likes_count,
            ];
        });
    }

    /**
     * Tekil gönderi detayını ilişkileriyle birlikte getirir.
     */
    public function getPostById(int $postId): Post
    {
        return Post::query()
            ->with([
                'user:id,name,username,avatar_path',
                'location:id,city,district',
            ])
            ->findOrFail($postId);
    }
}
