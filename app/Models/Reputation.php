<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reputation extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'points',
        'level',
        'badges',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'badges' => 'array',
        ];
    }

    /**
     * İtibara sahip kullanıcı.
     *
     * @return BelongsTo<User, Reputation>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Seviyeye karşılık gelen dinamik rozet simgesi.
     */
    public function getBadgeIconAttribute(): string
    {
        return match ($this->level) {
            'Gönüllü Muhtar' => '🎖️',
            'Mahalle Esnafı' => '🏪',
            'Mahalle Sakini' => '🏘️',
            default => '🌱',
        };
    }

    /**
     * Seviyeye karşılık gelen Tailwind CSS renk şeması.
     *
     * @return array{bg: string, text: string, border: string}
     */
    public function getBadgeThemeAttribute(): array
    {
        return match ($this->level) {
            'Gönüllü Muhtar' => [
                'bg' => 'bg-amber-100 dark:bg-amber-950/60',
                'text' => 'text-amber-800 dark:text-amber-300',
                'border' => 'border-amber-300 dark:border-amber-800',
            ],
            'Mahalle Esnafı' => [
                'bg' => 'bg-emerald-100 dark:bg-emerald-950/60',
                'text' => 'text-emerald-800 dark:text-emerald-300',
                'border' => 'border-emerald-300 dark:border-emerald-800',
            ],
            'Mahalle Sakini' => [
                'bg' => 'bg-blue-100 dark:bg-blue-950/60',
                'text' => 'text-blue-800 dark:text-blue-300',
                'border' => 'border-blue-300 dark:border-blue-800',
            ],
            default => [
                'bg' => 'bg-slate-100 dark:bg-neutral-800',
                'text' => 'text-slate-700 dark:text-neutral-300',
                'border' => 'border-slate-300 dark:border-neutral-700',
            ],
        };
    }
}

