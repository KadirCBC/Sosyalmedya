<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Like;
use App\Models\Post;

class LikeObserver
{
    /**
     * Beğeni eklendiğinde posts.likes_count değerini atomik olarak 1 artırır.
     */
    public function created(Like $like): void
    {
        Post::query()
            ->where('id', $like->post_id)
            ->increment('likes_count');
    }

    /**
     * Beğeni silindiğinde posts.likes_count değerini atomik olarak 1 azaltır.
     */
    public function deleted(Like $like): void
    {
        Post::query()
            ->where('id', $like->post_id)
            ->where('likes_count', '>', 0)
            ->decrement('likes_count');
    }
}

