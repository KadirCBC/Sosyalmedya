<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustCloudflareProxies
{
    /**
     * Cloudflare IP başlığını (CF-Connecting-IP) okuyarak istemcinin gerçek IP'sini belirler.
     * Bu sayede rate-limiting ve 5651 logları Cloudflare proxy IP'lerini değil, gerçek ziyaretçiyi baz alır.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cloudflare tarafından iletilen gerçek ziyaretçi IP'si
        if ($cfConnectingIp = $request->header('CF-Connecting-IP')) {
            $request->server->set('REMOTE_ADDR', $cfConnectingIp);
        } elseif ($forwardedFor = $request->header('X-Forwarded-For')) {
            $ips = explode(',', $forwardedFor);
            $clientIp = trim($ips[0]);
            if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
                $request->server->set('REMOTE_ADDR', $clientIp);
            }
        }

        return $next($request);
    }
}

