<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistrictSentimentIndex extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'location_id',
        'date',
        'positive_score',
        'negative_score',
        'total_posts_analyzed',
        'total_complaints_detected',
        'top_complaint_topics',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'positive_score' => 'float',
            'negative_score' => 'float',
            'total_posts_analyzed' => 'integer',
            'total_complaints_detected' => 'integer',
            'top_complaint_topics' => 'array',
        ];
    }

    /**
     * Endeksin ait olduğu ilçe.
     *
     * @return BelongsTo<Location, DistrictSentimentIndex>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}

