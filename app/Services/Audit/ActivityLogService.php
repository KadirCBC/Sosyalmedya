<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogService
{
    /**
     * 5651 Sayılı Kanun ve KVKK standartlarında trafik ve işlem kaydı oluşturur.
     *
     * @param string $action Eylem tanımı (örn: 'post_create', 'comment_create', 'login')
     * @param string|null $content İçerik (özet parmak izi almak için SHA-256'ya çevrilir)
     * @param string|null $targetType İlgili model sınıfı (örn: App\Models\Post)
     * @param int|null $targetId İlgili kaydın ID'si
     */
    public function log(
        Request $request,
        string $action,
        ?User $user = null,
        ?string $content = null,
        ?string $targetType = null,
        ?int $targetId = null
    ): ActivityLog {
        $user = $user ?? $request->user('sanctum');

        // IP adresi ve CGNAT tespitinde yasal zorunluluk olan kaynak Port bilgisi
        $ip = (string) ($request->header('CF-Connecting-IP') ?? $request->header('X-Forwarded-For') ?? $request->ip() ?? '127.0.0.1');
        // Virgülden önceki ilk gerçek IP'yi al (Proxy zincirlerinde)
        if (str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }

        $port = $request->server('REMOTE_PORT');
        $portInt = is_numeric($port) ? (int) $port : null;

        // KVKK ve delil bütünlüğü için içeriğin SHA-256 kriptografik özeti
        $payloadHash = $content !== null ? hash('sha256', $content) : null;

        return ActivityLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'ip_address' => substr($ip, 0, 45),
            'port' => $portInt,
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'target_type' => $targetType,
            'target_id' => $targetId,
            'payload_hash' => $payloadHash,
            'created_at' => now(),
        ]);
    }
}

