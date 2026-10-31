<?php

declare(strict_types=1);

namespace App\Services\Reputation;

use App\Models\Reputation;
use App\Models\ReputationLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReputationService
{
    // Puan Katsayıları
    public const POINTS_LOCAL_LIKE = 5;          // Kendi mahallesinden beğeni alma
    public const POINTS_POST_CREATED = 2;        // Faydalı gönderi paylaşma
    public const POINTS_COMMENT_CREATED = 1;     // Yorumla katkı sağlama
    public const POINTS_HELPFUL_ANSWER = 3;      // Faydalı yanıt / çözüm sunma
    public const POINTS_REPORT_CONFIRMED = -50;  // Şikayet onaylanıp içerik kaldırıldığında
    public const POINTS_USER_SUSPENDED = -100;   // Hesap askıya alındığında

    /**
     * Kullanıcıya itibar puanı ekler veya düşer, seviyesini ve rozetlerini otomatik günceller.
     */
    public function adjustPoints(int $userId, string $action, int $pointsDelta, ?string $description = null): Reputation
    {
        return DB::transaction(function () use ($userId, $action, $pointsDelta, $description) {
            /** @var User $user */
            $user = User::query()->findOrFail($userId);

            /** @var Reputation $reputation */
            $reputation = Reputation::query()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'points' => 0,
                    'level' => 'Yeni Komşu',
                    'badges' => ['🌱 Yeni Komşu'],
                ]
            );

            // 1. Puanı güncelle (0'ın altına düşebilir fakat troll hesapları eksi puana düşürmek yararlıdır)
            $newPoints = $reputation->points + $pointsDelta;
            $reputation->points = $newPoints;

            // 2. Seviye ve Unvan Hesaplaması
            $reputation->level = $this->calculateLevel($user, $newPoints);

            // 3. Rozetleri Senkronize Et
            $reputation->badges = $this->resolveBadges($user, $newPoints, $reputation->level);
            $reputation->save();

            // 4. Denetim ve Hareket Kaydı (ReputationLog)
            ReputationLog::query()->create([
                'user_id' => $user->id,
                'action' => $action,
                'points_delta' => $pointsDelta,
                'description' => $description,
            ]);

            // 5. İlgili ilçenin liderlik tablosu önbelleğini temizle
            if ($user->location_id) {
                Cache::forget("district_leaderboard_{$user->location_id}");
            }

            return $reputation;
        });
    }

    /**
     * Mahalleden beğeni alındığında tetiklenir (+5 Puan).
     */
    public function awardLocalLike(int $authorUserId, string $likerName): Reputation
    {
        return $this->adjustPoints(
            $authorUserId,
            'local_like_received',
            self::POINTS_LOCAL_LIKE,
            "{$likerName} mahalle gönderini beğendi"
        );
    }

    /**
     * Yeni gönderi oluşturulduğunda tetiklenir (+2 Puan).
     */
    public function awardPostCreated(int $userId): Reputation
    {
        return $this->adjustPoints(
            $userId,
            'post_created',
            self::POINTS_POST_CREATED,
            'Mahalle akışında yeni gönderi paylaşıldı'
        );
    }

    /**
     * Yorum yapıldığında tetiklenir (+1 Puan).
     */
    public function awardCommentCreated(int $userId): Reputation
    {
        return $this->adjustPoints(
            $userId,
            'comment_created',
            self::POINTS_COMMENT_CREATED,
            'Mahalle tartışmasına yorumla katkı sağlandı'
        );
    }

    /**
     * İçerik şikayet edilip moderatörce onaylandığında ağır ceza (-50 Puan).
     */
    public function penalizeViolation(int $userId, string $reason = 'İhlal bildirimli içerik kaldırıldı'): Reputation
    {
        return $this->adjustPoints(
            $userId,
            'report_confirmed',
            self::POINTS_REPORT_CONFIRMED,
            "Moderasyon cezası: {$reason}"
        );
    }

    /**
     * Kullanıcı askıya alındığında ekstra ceza (-100 Puan).
     */
    public function penalizeSuspension(int $userId): Reputation
    {
        return $this->adjustPoints(
            $userId,
            'user_suspended',
            self::POINTS_USER_SUSPENDED,
            'Hesap 24 saat askıya alındı'
        );
    }

    /**
     * Puan ve kullanıcı durumuna göre unvan hesaplar.
     */
    public function calculateLevel(User $user, int $points): string
    {
        // Eğer kullanıcı esnaf rolüne sahipse veya doğrulanmış yerel işletmeyse
        if ($user->role?->value === 'merchant') {
            return 'Mahalle Esnafı';
        }

        if ($points >= 500) {
            return 'Gönüllü Muhtar';
        }

        if ($points >= 101) {
            return 'Mahalle Sakini';
        }

        return 'Yeni Komşu';
    }

    /**
     * Puan ve role göre hak edilen rozet listesini üretir.
     *
     * @return list<string>
     */
    public function resolveBadges(User $user, int $points, string $level): array
    {
        $badges = [];

        // Ana seviye rozeti
        switch ($level) {
            case 'Gönüllü Muhtar':
                $badges[] = '🎖️ Gönüllü Muhtar';
                $badges[] = '🛡️ Moderasyon Adayı';
                break;
            case 'Mahalle Esnafı':
                $badges[] = '🏪 Mahalle Esnafı';
                $badges[] = '⭐ Doğrulanmış İşletme';
                break;
            case 'Mahalle Sakini':
                $badges[] = '🏘️ Mahalle Sakini';
                break;
            default:
                $badges[] = '🌱 Yeni Komşu';
                break;
        }

        if ($points >= 1000) {
            $badges[] = '🏆 İlçe Efsanesi';
        }

        return $badges;
    }

    /**
     * Belirli bir ilçedeki en yüksek itibarlı ilk 10 komşuyu getirir (Liderlik Tablosu).
     * 5 dakikalık Redis/File önbelleği ile korunur.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getDistrictLeaderboard(int $locationId, int $limit = 10): Collection
    {
        $cacheKey = "district_leaderboard_{$locationId}";

        return Cache::remember($cacheKey, 300, function () use ($locationId, $limit) {
            $users = User::query()
                ->where('location_id', $locationId)
                ->where(function ($q) {
                    $q->whereNull('suspended_until')
                        ->orWhere('suspended_until', '<=', now());
                })
                ->join('reputations', 'users.id', '=', 'reputations.user_id')
                ->select([
                    'users.id',
                    'users.name',
                    'users.username',
                    'users.avatar_path',
                    'users.role',
                    'reputations.points',
                    'reputations.level',
                    'reputations.badges',
                ])
                ->orderByDesc('reputations.points')
                ->take($limit)
                ->get();

            return $users->values()->map(function ($u, $index) {
                return [
                    'rank' => $index + 1,
                    'user_id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'avatar_url' => $u->avatar_path ? asset($u->avatar_path) : null,
                    'points' => (int) $u->points,
                    'level' => $u->level ?? 'Yeni Komşu',
                    'badge_icon' => match ($u->level) {
                        'Gönüllü Muhtar' => '🎖️',
                        'Mahalle Esnafı' => '🏪',
                        'Mahalle Sakini' => '🏘️',
                        default => '🌱',
                    },
                    'badges' => is_array($u->badges) ? $u->badges : json_decode((string) $u->badges, true) ?? [],
                ];
            });
        });
    }
}

