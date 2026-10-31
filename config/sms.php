<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Varsayılan SMS Sürücüsü
    |--------------------------------------------------------------------------
    | Desteklenenler: "netgsm", "log"
    */
    'default' => env('SMS_DRIVER', 'netgsm'),

    'drivers' => [
        'netgsm' => [
            'usercode' => env('NETGSM_USERCODE', ''),
            'password' => env('NETGSM_PASSWORD', ''),
            'header' => env('NETGSM_HEADER', ''),
            'endpoint' => env('NETGSM_ENDPOINT', 'https://api.netgsm.com.tr/sms/send/xml'),
            'timeout' => (int) env('NETGSM_TIMEOUT', 10),
        ],

        'log' => [
            'channel' => env('SMS_LOG_CHANNEL', 'single'),
        ],
    ],
];

