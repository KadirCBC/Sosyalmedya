<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    public function __construct(
        protected PostService $postService
    ) {}

    /**
     * Yeni bir gönderi paylaşır.
     */
    public function store(CreatePostRequest $request): JsonResponse
    {
        $images = $request->file('images', []);
        if (! is_array($images)) {
            $images = [$images];
        }

        $post = $this->postService->createPost(
            user: $request->user(),
            data: $request->validated(),
            images: $images
        );

        return response()->json([
            'message' => 'Gönderi başarıyla paylaşıldı.',
            'post' => new PostResource($post),
        ], Response::HTTP_CREATED);
    }

    /**
     * Tekil gönderi detayını getirir.
     */
    public function show(int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id);

        return response()->json([
            'post' => new PostResource($post),
        ], Response::HTTP_OK);
    }

    /**
     * Gönderiyi beğenir veya beğeniyi geri çeker (toggle).
     */
    public function toggleLike(Request $request, int $id): JsonResponse
    {
        $result = $this->postService->toggleLike($request->user(), $id);

        return response()->json([
            'message' => $result['is_liked'] ? 'Gönderi beğenildi.' : 'Beğeni geri çekildi.',
            'is_liked' => $result['is_liked'],
            'likes_count' => $result['likes_count'],
        ], Response::HTTP_OK);
    }
}

