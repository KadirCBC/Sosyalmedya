<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | WebPush VAPID Anahtarları
    |--------------------------------------------------------------------------
    | Tarayıcı bildirimleri göndermek için standart Voluntary Application Server
    | Identification (VAPID) anahtarları.
    */
    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@meydan.com'),
        'public_key' => env('VAPID_PUBLIC_KEY', 'BKp_7YwT_sample_public_key_for_webpush_vapid_auth_in_browser_meydan_64_chars'),
        'private_key' => env('VAPID_PRIVATE_KEY', 'sample_private_key_for_webpush_vapid_server_signing_meydan_43_chars'),
    ],

    /*
    | Varsayılan bildirim simgesi ve logosu
    */
    'default_icon' => env('WEBPUSH_DEFAULT_ICON', '/icons/icon-192x192.png'),
    'default_badge' => env('WEBPUSH_DEFAULT_BADGE', '/icons/badge-72x72.png'),

    /*
    | TTL (Time To Live - saniye cinsinden)
    | Cihaz çevrimdışıysa bildirim mesajının kuyrukta kalma süresi (varsayılan 24 saat)
    */
    'ttl' => 86400,

];
