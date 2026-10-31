<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Location;
use App\Models\Post;
use App\Services\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeedWebController extends Controller
{
    /**
     * Ana sayfa çift akış (Feed) arayüzünü döndürür.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Kullanıcının kayıtlı olduğu lokasyon veya varsayılan lokasyon
        $location = $user?->location ?? Location::query()->first();

        $districtName = $location?->district ?? 'Kadıköy';
        $locationId = $location?->id ?? 1;

        return view('feed.index', [
            'districtName' => $districtName,
            'locationId' => $locationId,
        ]);
    }

    /**
     * Tekil gönderi sayfasını dinamik OpenGraph ve Twitter Card meta etiketleriyle sunar.
     */
    public function showPost(int $id, MediaService $mediaService): View
    {
        /** @var Post $postModel */
        $postModel = Post::query()
            ->with(['user', 'location'])
            ->findOrFail($id);

        $postResource = (new PostResource($postModel))->resolve();

        // Dinamik OpenGraph & Twitter Card SEO Metinleri
        $authorName = $postModel->user->name ?? 'Meydan Yazarı';
        $username = $postModel->user->username ?? 'user';
        $locationLabel = $postModel->location ? "📍 {$postModel->location->district}" : '🌍 Genel';

        $ogTitle = "{$authorName} (@{$username}) on Meydan: \"{$locationLabel}\"";
        $ogDescription = Str::limit((string) $postModel->content, 140, '...');

        // Eğer gönderide görsel varsa ilk görsel, yoksa avatar veya varsayılan banner
        $ogImage = ! empty($postResource['media_urls'])
            ? $postResource['media_urls'][0]
            : ($postResource['user']['avatar_url'] ?? 'https://pub-your-id.r2.dev/assets/meydan-og-banner.jpg');

        $ogUrl = url("/posts/{$id}");

        // Yorumlar
        $comments = $postModel->comments()
            ->with('user')
            ->orderByDesc('id')
            ->take(15)
            ->get()
            ->map(fn ($c) => (new CommentResource($c))->resolve());

        return view('posts.show', [
            'post' => $postResource,
            'comments' => $comments,
            'ogTitle' => $ogTitle,
            'ogDescription' => $ogDescription,
            'ogImage' => $ogImage,
            'ogUrl' => $ogUrl,
        ]);
    }
}
