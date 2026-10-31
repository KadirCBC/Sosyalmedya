<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Comment;
use App\Models\Post;

class CommentObserver
{
    /**
     * Yeni bir yorum eklendiğinde posts.comments_count değerini atomik olarak 1 artırır.
     */
    public function created(Comment $comment): void
    {
        Post::query()
            ->where('id', $comment->post_id)
            ->increment('comments_count');
    }

    /**
     * Yorum silindiğinde posts.comments_count değerini atomik olarak 1 azaltır.
     */
    public function deleted(Comment $comment): void
    {
        Post::query()
            ->where('id', $comment->post_id)
            ->where('comments_count', '>', 0)
            ->decrement('comments_count');
    }
}

