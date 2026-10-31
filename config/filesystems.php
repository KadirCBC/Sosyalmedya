<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Varsayılan Dosya Sistemi Diski
    |--------------------------------------------------------------------------
    */
    'default' => env('FILESYSTEM_DISK', 'r2'),

    /*
    |--------------------------------------------------------------------------
    | Dosya Sistemi Diskleri
    |--------------------------------------------------------------------------
    */
    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        // Cloudflare R2 Depolama Diski (S3 Sürücüsü ile Tam Uyumlu)
        'r2' => [
            'driver' => 's3',
            'key' => env('R2_ACCESS_KEY_ID', env('CLOUDFLARE_R2_ACCESS_KEY_ID')),
            'secret' => env('R2_SECRET_ACCESS_KEY', env('CLOUDFLARE_R2_SECRET_ACCESS_KEY')),
            'region' => 'auto',
            'bucket' => env('R2_BUCKET', env('CLOUDFLARE_R2_BUCKET')),
            'url' => env('R2_URL', env('CLOUDFLARE_R2_URL')),
            'endpoint' => env('R2_ENDPOINT', env('CLOUDFLARE_R2_ENDPOINT')),
            'use_path_style_endpoint' => env('R2_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Sembolik Bağlantılar
    |--------------------------------------------------------------------------
    */
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];

