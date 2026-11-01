<?php

declare(strict_types=1);

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Bakım modu kontrolü
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// 1. Standart Composer Autoloader kontrolü
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';

    $response = $app->handleRequest(Request::capture());
    $response->send();
    exit;
}

// 2. Standalone Web Runner (Vendor henüz yüklenmemişse doğrudan localhost:5000 üzerinde arayüzü sunar)
require_once __DIR__.'/../app/standalone_runner.php';

