<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistrictTrend extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'location_id',
        'term',
        'is_hashtag',
        'frequency',
        'positive_hits',
        'negative_hits',
        'date',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_hashtag' => 'boolean',
            'frequency' => 'integer',
            'positive_hits' => 'integer',
            'negative_hits' => 'integer',
            'date' => 'date',
        ];
    }

    /**
     * Trendin ait olduğu ilçe.
     *
     * @return BelongsTo<Location, DistrictTrend>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}

