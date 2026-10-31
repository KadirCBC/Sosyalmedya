<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrModerator
{
    /**
     * Yönetici ve moderatör yetki kontrolü middleware'i.
     */
    public function handle(Request $request, Closure $next, ?string $requiredRole = null): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Lütfen önce giriş yapınız.');
        }

        if ($user->isSuspended()) {
            auth()->logout();
            return redirect()->route('home')->with('error', "Hesabınız {$user->suspended_until?->format('d.m.Y H:i')} tarihine kadar askıya alınmıştır.");
        }

        // Süper Admin her yere erişebilir
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Eğer belirli bir rol zorunlu tutulduysa (örn: super_admin)
        if ($requiredRole === 'super_admin' && ! $user->isSuperAdmin()) {
            abort(Response::HTTP_FORBIDDEN, 'Bu alana yalnızca Süper Yöneticiler erişebilir.');
        }

        // Bölge moderatörü kontrolü
        if ($user->isLocalModerator()) {
            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN, 'Bu yönetim paneline erişim yetkiniz bulunmamaktadır.');
    }
}

