<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MediaService
{
    /**
     * Varsayılan R2 disk adı.
     */
    protected string $disk;

    /**
     * WebP sıkıştırma kalitesi (0-100).
     */
    protected int $webpQuality = 82;

    public function __construct()
    {
        $this->disk = (string) config('filesystems.default', 'r2');
    }

    /**
     * Yüklenen görsel dosyasını WebP formatına dönüştürüp sıkıştırarak Cloudflare R2'ye yükler.
     *
     * @param UploadedFile $file
     * @param string $directory R2 içindeki hedef klasör (örn: 'posts', 'avatars')
     * @return string R2 üzerindeki göreli dosya yolu (örn: 'posts/uuid.webp')
     */
    public function uploadImageAsWebp(UploadedFile $file, string $directory = 'posts'): string
    {
        $filename = (string) Str::uuid() . '.webp';
        $targetPath = trim($directory, '/') . '/' . $filename;

        // Görseli WebP formatına dönüştür ve sıkıştır
        $webpContent = $this->convertToWebp($file);

        // Cloudflare R2'ye aktar
        $uploaded = Storage::disk($this->disk)->put($targetPath, $webpContent, [
            'visibility' => 'public',
            'mimetype' => 'image/webp',
            'CacheControl' => 'max-age=31536000, public', // 1 yıl CDN önbelleği
        ]);

        if (! $uploaded) {
            throw new RuntimeException("Görsel Cloudflare R2 depolamasına yüklenemedi: {$targetPath}");
        }

        return $targetPath;
    }

    /**
     * Birden fazla görseli toplu olarak WebP'ye çevirip yükler.
     *
     * @param list<UploadedFile> $files
     * @return list<string>
     */
    public function uploadMultipleImages(array $files, string $directory = 'posts'): array
    {
        $paths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $this->uploadImageAsWebp($file, $directory);
            }
        }

        return $paths;
    }

    /**
     * Göreli dosya yolunu tam CDN/R2 URL adresine çevirir.
     */
    public function getUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $baseUrl = rtrim((string) env('CLOUDFLARE_R2_URL', config("filesystems.disks.{$this->disk}.url", '')), '/');

        if (! empty($baseUrl)) {
            return $baseUrl . '/' . ltrim($path, '/');
        }

        return Storage::disk($this->disk)->url($path);
    }

    /**
     * PHP GD kütüphanesi ile görseli optimize WebP formatına dönüştürür.
     */
    protected function convertToWebp(UploadedFile $file): string
    {
        $mime = $file->getMimeType();
        $sourcePath = $file->getRealPath();

        if (! function_exists('imagecreatefromstring')) {
            // GD yüklü değilse orijinal içeriği döner
            return (string) file_get_contents($sourcePath);
        }

        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($sourcePath),
            'image/png' => $this->createFromPngPreservingAlpha($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => imagecreatefromstring((string) file_get_contents($sourcePath)),
        };

        if ($image === false) {
            return (string) file_get_contents($sourcePath);
        }

        ob_start();
        // WebP formatında çıktı üret (kalite: 82)
        imagewebp($image, null, $this->webpQuality);
        $content = (string) ob_get_clean();

        imagedestroy($image);

        return $content;
    }

    /**
     * PNG görsellerin şeffaflık (alpha) kanalını koruyarak yükler.
     *
     * @return \GdImage|resource|false
     */
    protected function createFromPngPreservingAlpha(string $filePath)
    {
        $image = imagecreatefrompng($filePath);

        if ($image !== false) {
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        return $image;
    }
}

