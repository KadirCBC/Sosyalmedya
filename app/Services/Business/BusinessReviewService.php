<?php

declare(strict_types=1);

namespace App\Services\Business;

use App\Models\BusinessProfile;
use App\Models\BusinessReview;
use App\Models\User;
use App\Services\Moderation\ContentFilterService;
use App\Services\Reputation\ReputationService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class BusinessReviewService
{
    public function __construct(
        protected ContentFilterService $contentFilterService,
        protected ReputationService $reputationService
    ) {}

    /**
     * İşletmeye müşteri puanı ve yorumu ekler, puan ortalamasını atomik olarak tazeler.
     */
    public function addReview(User $user, int $businessProfileId, int $rating, string $comment): BusinessReview
    {
        /** @var BusinessProfile $profile */
        $profile = BusinessProfile::query()->findOrFail($businessProfileId);

        if ($profile->user_id === $user->id) {
            throw new BadRequestHttpException('Kendi işletmenize değerlendirme yapamazsınız.');
        }

        if ($rating < 1 || $rating > 5) {
            throw new BadRequestHttpException('Puan 1 ile 5 arasında olmalıdır.');
        }

        $cleanComment = $this->contentFilterService->inspectAndSanitize($comment);

        return DB::transaction(function () use ($user, $profile, $rating, $cleanComment) {
            /** @var BusinessReview $review */
            $review = BusinessReview::query()->updateOrCreate(
                [
                    'business_profile_id' => $profile->id,
                    'user_id' => $user->id,
                ],
                [
                    'rating' => $rating,
                    'comment' => $cleanComment,
                ]
            );

            // Puan ortalamasını ve toplam yorum sayısını atomik olarak güncelle
            $stats = BusinessReview::query()
                ->where('business_profile_id', $profile->id)
                ->selectRaw('COUNT(*) as total_count, AVG(rating) as avg_rating')
                ->first();

            $totalCount = (int) ($stats->total_count ?? 0);
            $avgRating = round((float) ($stats->avg_rating ?? 0.00), 2);

            $profile->update([
                'reviews_count' => $totalCount,
                'rating_avg' => $avgRating,
            ]);

            // Değerlendirme yapan mahalleliye +2 itibar puanı ver
            $this->reputationService->adjustPoints(
                $user->id,
                'business_review_created',
                2,
                "{$profile->business_name} esnafına değerlendirme yapıldı"
            );

            return $review->load('user:id,name,username,avatar_path');
        });
    }
}

