<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'phone',
        'phone_verified_at',
        'password',
        'location_id',
        'avatar_path',
        'bio',
        'role',
        'moderated_location_id',
        'suspended_until',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'suspended_until' => 'datetime',
        ];
    }

    /**
     * Kullanıcının süper yönetici olup olmadığını kontrol eder.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    /**
     * Kullanıcının bölge moderatörü olup olmadığını kontrol eder.
     */
    public function isLocalModerator(): bool
    {
        return $this->role === UserRole::LOCAL_MODERATOR;
    }

    /**
     * Belirtilen lokasyonu modere etme yetkisi olup olmadığını kontrol eder.
     */
    public function canModerateLocation(?int $locationId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->isLocalModerator() && $locationId !== null) {
            return (int) $this->moderated_location_id === (int) $locationId;
        }

        return false;
    }

    /**
     * Kullanıcı hesabının askıya alınıp alınmadığını denetler.
     */
    public function isSuspended(): bool
    {
        return $this->suspended_until !== null && $this->suspended_until->isFuture();
    }

    /**
     * Moderatörün yetkili olduğu ilçe/şehir.
     *
     * @return BelongsTo<Location, User>
     */
    public function moderatedLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'moderated_location_id');
    }

    /**
     * Kullanıcının kayıtlı olduğu lokasyon.
     *
     * @return BelongsTo<Location, User>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Kullanıcının WebPush tarayıcı bildirim abonelikleri.
     *
     * @return HasMany<PushSubscription>
     */
    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    /**
     * Kullanıcının paylaştığı gönderiler.
     *
     * @return HasMany<Post>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Kullanıcının bıraktığı beğeniler (Like pivot modeli).
     *
     * @return HasMany<Like>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Kullanıcının beğendiği tüm gönderiler.
     *
     * @return BelongsToMany<Post>
     */
    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'likes')
            ->withTimestamps();
    }

    /**
     * Bu kullanıcıyı takip edenler (Followers).
     *
     * @return BelongsToMany<User>
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    /**
     * Bu kullanıcının takip ettiği kişiler (Following).
     *
     * @return BelongsToMany<User>
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    /**
     * Kullanıcının başlattığı takip kayıtları.
     *
     * @return HasMany<Follow>
     */
    public function givenFollows(): HasMany
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    /**
     * Kullanıcıya gelen takip kayıtları.
     *
     * @return HasMany<Follow>
     */
    public function receivedFollows(): HasMany
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    /**
     * Kullanıcının yerel itibar ve rozet bilgisi.
     *
     * @return HasOne<Reputation>
     */
    public function reputation(): HasOne
    {
        return $this->hasOne(Reputation::class);
    }

    /**
     * Kullanıcının itibar puan hareketleri.
     *
     * @return HasMany<ReputationLog>
     */
    public function reputationLogs(): HasMany
    {
        return $this->hasMany(ReputationLog::class);
    }

    /**
     * Esnaf kullanıcısının işletme profili.
     *
     * @return HasOne<BusinessProfile>
     */
    public function businessProfile(): HasOne
    {
        return $this->hasOne(BusinessProfile::class);
    }

    /**
     * Kullanıcının doğrulanmış bir işletmeye sahip olup olmadığını kontrol eder.
     */
    public function isVerifiedMerchant(): bool
    {
        return $this->businessProfile !== null && $this->businessProfile->is_verified;
    }
}

