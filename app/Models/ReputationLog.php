<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReputationLog extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'points_delta',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points_delta' => 'integer',
        ];
    }

    /**
     * İşlemin sahibi olan kullanıcı.
     *
     * @return BelongsTo<User, ReputationLog>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

