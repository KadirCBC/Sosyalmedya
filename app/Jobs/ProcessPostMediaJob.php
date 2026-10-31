<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enums\PostScope;
use App\Models\Post;
use App\Services\MediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessPostMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    /**
     * @param int $postId İşlenecek gönderinin ID'si
     * @param list<string> $tempPaths Sunucu geçici klasöründe saklanan dosya yolları
     */
    public function __construct(
        public int $postId,
        public array $tempPaths
    ) {}

    /**
     * Arka planda görselleri WebP'ye dönüştürüp Cloudflare R2'ye yükler.
     */
    public function handle(MediaService $mediaService): void
    {
        /** @var Post|null $post */
        $post = Post::query()->find($this->postId);

        if (! $post) {
            $this->cleanupTempFiles();
            return;
        }

        $mediaPaths = [];

        foreach ($this->tempPaths as $tempPath) {
            if (! file_exists($tempPath)) {
                continue;
            }

            $uploadedFile = new UploadedFile(
                path: $tempPath,
                originalName: basename($tempPath),
                mimeType: mime_content_type($tempPath) ?: 'image/jpeg',
                error: null,
                test: true
            );

            $mediaPaths[] = $mediaService->uploadImageAsWebp($uploadedFile, 'posts');
        }

        // Gönderi medyasını güncelle
        $post->update([
            'media_paths' => ! empty($mediaPaths) ? $mediaPaths : null,
        ]);

        // Geçici dosyaları temizle
        $this->cleanupTempFiles();

        // Eğer gönderi yerel akışa aitse, o ilçenin önbelleğini temizle (cache invalidation)
        if ($post->scope === PostScope::LOCAL && $post->location_id !== null) {
            Cache::forget("feed:local:{$post->location_id}:page:1");
        }
    }

    /**
     * Geçici yükleme dosyalarını sunucudan siler.
     */
    protected function cleanupTempFiles(): void
    {
        foreach ($this->tempPaths as $tempPath) {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * İş kalıcı olarak başarısız olduğunda geçici dosyaları temizler.
     */
    public function failed(?Throwable $exception): void
    {
        $this->cleanupTempFiles();

        Log::error('ProcessPostMediaJob başarısız oldu', [
            'post_id' => $this->postId,
            'error' => $exception?->getMessage(),
        ]);
    }
}

