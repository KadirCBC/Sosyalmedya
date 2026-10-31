<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    public function __construct(
        protected CommentService $commentService
    ) {}

    /**
     * Bir gönderiye ait yorumları (cursor pagination ile) listeler.
     */
    public function index(Request $request, int $postId): AnonymousResourceCollection
    {
        $parentId = $request->query('parent_id') !== null ? (int) $request->query('parent_id') : null;
        $cursor = $request->query('cursor');

        $comments = $this->commentService->getComments(
            postId: $postId,
            parentId: $parentId,
            cursor: is_string($cursor) ? $cursor : null
        );

        return CommentResource::collection($comments);
    }

    /**
     * Gönderiye yeni bir yorum veya yanıta alt yorum ekler.
     */
    public function store(CreateCommentRequest $request, int $postId): JsonResponse
    {
        $comment = $this->commentService->createComment(
            user: $request->user(),
            postId: $postId,
            data: $request->validated()
        );

        return response()->json([
            'message' => 'Yorum başarıyla paylaşıldı.',
            'comment' => new CommentResource($comment),
        ], Response::HTTP_CREATED);
    }

    /**
     * Yorumu siler.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->commentService->deleteComment(
            user: $request->user(),
            commentId: $id
        );

        return response()->json([
            'message' => 'Yorum başarıyla silindi.',
        ], Response::HTTP_OK);
    }
}

