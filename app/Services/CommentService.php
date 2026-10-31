<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Notifications\PostInteractedNotification;
use App\Services\Audit\ActivityLogService;
use App\Services\Moderation\ContentFilterService;
use App\Services\Reputation\ReputationService;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CommentService
{
    public function __construct(
        protected ContentFilterService $contentFilterService,
        protected ActivityLogService $activityLogService,
        protected ReputationService $reputationService
    ) {}
    /**
     * Belirli bir gönderiye ait yorumları (veya bir yorumun yanıtlarını) cursor pagination ile getirir.
     *
     * @return CursorPaginator<Comment>
     */
    public function getComments(int $postId, ?int $parentId = null, ?string $cursor = null): CursorPaginator
    {
        return Comment::query()
            ->where('post_id', $postId)
            ->where('parent_id', $parentId)
            ->orderByDesc('id')
            ->with([
                'user:id,name,username,avatar_path',
            ])
            ->withCount('replies')
            ->cursorPaginate(
                perPage: 20,
                cursor: $cursor
            );
    }

    /**
     * Gönderiye yorum veya yanıta alt yorum ekler.
     *
     * @param array{content: string, parent_id?: int|null} $data
     */
    public function createComment(User $user, int $postId, array $data): Comment
    {
        /** @var Post $post */
        $post = Post::query()->findOrFail($postId);

        $parentId = ! empty($data['parent_id']) ? (int) $data['parent_id'] : null;
        $parentComment = null;

        if ($parentId !== null) {
            /** @var Comment $parentComment */
            $parentComment = Comment::query()
                ->where('id', $parentId)
                ->where('post_id', $postId)
                ->firstOrFail();
        }

        // Mevzuat ve Güvenlik Filtresi (TCKN, IBAN, Yasadışı Bahis, Küfür Maskeleme)
        $cleanContent = $this->contentFilterService->inspectAndSanitize((string) $data['content']);

        /** @var Comment $comment */
        $comment = DB::transaction(function () use ($user, $post, $parentId, $cleanContent) {
            return Comment::query()->create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'parent_id' => $parentId,
                'content' => $cleanContent,
            ]);
        });

        // 5651 Sayılı Kanun Yer Sağlayıcı Trafik ve İçerik Kaydı (IP, Port, SHA-256 Hash)
        $this->activityLogService->log(
            request: request(),
            action: 'comment_create',
            user: $user,
            content: $cleanContent,
            targetType: Comment::class,
            targetId: $comment->id
        );

        // 1. Gönderi sahibine bildirim gönder (kendi gönderisi değilse)
        if ($post->user_id !== $user->id) {
            $post->user->notify(new PostInteractedNotification($user, $post, 'comment', $comment->content));
        }

        // 2. Bir yoruma yanıt verildiyse ve yanıtlanan kişi post sahibinden ve yorum yapandan farklıysa ona da bildirim gönder
        if ($parentComment && $parentComment->user_id !== $user->id && $parentComment->user_id !== $post->user_id) {
            $parentComment->user->notify(new PostInteractedNotification($user, $post, 'comment', $comment->content));
        }

        // Yerel İtibar: Yorumla katkı puanı ekle (+1 Puan)
        $this->reputationService->awardCommentCreated($user->id);

        return $comment->load([
            'user:id,name,username,avatar_path',
            'user.reputation',
        ]);
    }

    /**
     * Yorumu siler. Yorumu sadece yorum sahibi veya gönderi sahibi silebilir.
     */
    public function deleteComment(User $user, int $commentId): bool
    {
        /** @var Comment $comment */
        $comment = Comment::query()
            ->with('post:id,user_id')
            ->findOrFail($commentId);

        // İzin kontrolü: Yorum sahibi veya post sahibi silebilir
        if ($comment->user_id !== $user->id && $comment->post->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Bu yorumu silme yetkiniz bulunmamaktadır.');
        }

        return (bool) $comment->delete();
    }
}

