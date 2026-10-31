<?php

declare(strict_types=1);

namespace App\Services\Poll;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use App\Models\Post;
use App\Models\User;
use App\Services\Reputation\ReputationService;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PollService
{
    public function __construct(
        protected ReputationService $reputationService
    ) {}

    /**
     * Gönderiye bağlı yeni bir yerel anket oluşturur (2-4 seçenekli).
     *
     * @param list<string> $options
     */
    public function createPollForPost(Post $post, string $question, array $options, int $durationHours = 24): Poll
    {
        // 2-4 seçenek kontrolü
        $cleanOptions = array_values(array_filter(array_map('trim', $options)));
        if (count($cleanOptions) < 2 || count($cleanOptions) > 4) {
            throw new InvalidArgumentException('Bir anket en az 2, en fazla 4 seçenek içermelidir.');
        }

        $durationHours = max(1, min(168, $durationHours)); // 1 saat - 7 gün arası

        return DB::transaction(function () use ($post, $question, $cleanOptions, $durationHours) {
            /** @var Poll $poll */
            $poll = Poll::query()->create([
                'post_id' => $post->id,
                'question' => trim($question),
                'expires_at' => now()->addHours($durationHours),
                'total_votes' => 0,
            ]);

            foreach ($cleanOptions as $optionText) {
                PollOption::query()->create([
                    'poll_id' => $poll->id,
                    'option_text' => $optionText,
                    'votes_count' => 0,
                ]);
            }

            return $poll->load('options');
        });
    }

    /**
     * Atomik işlem ile ankete oy kullanır.
     * Mükerrer oy engeli, süre dolumu kontrolü ve sayaç artırımı uygular.
     *
     * @return array<string, mixed>
     */
    public function vote(User $user, int $pollId, int $optionId): array
    {
        return DB::transaction(function () use ($user, $pollId, $optionId) {
            /** @var Poll $poll */
            $poll = Poll::query()->with('post.location')->lockForUpdate()->findOrFail($pollId);

            // 1. Süre dolumu kontrolü
            if ($poll->isExpired()) {
                throw new DomainException('Bu anketin oylama süresi dolmuştur.');
            }

            // 2. Seçenek ankete mi ait?
            /** @var PollOption $option */
            $option = PollOption::query()
                ->where('poll_id', $poll->id)
                ->where('id', $optionId)
                ->lockForUpdate()
                ->firstOrFail();

            // 3. Mükerrer oy kontrolü
            $alreadyVoted = PollVote::query()
                ->where('poll_id', $poll->id)
                ->where('user_id', $user->id)
                ->exists();

            if ($alreadyVoted) {
                throw new DomainException('Bu ankette daha önce oy kullandınız.');
            }

            // 4. Oy kaydı oluştur
            PollVote::query()->create([
                'poll_id' => $poll->id,
                'poll_option_id' => $option->id,
                'user_id' => $user->id,
            ]);

            // 5. Sayaçları atomik artır
            $option->increment('votes_count');
            $poll->increment('total_votes');

            // 6. Yerel sivil katılım puanı (+1 Puan)
            $this->reputationService->adjustPoints(
                userId: $user->id,
                action: 'poll_vote',
                pointsDelta: 1,
                description: 'Yerel ankette oy kullanarak mahalle kararına katılım sağladı'
            );

            // Yenilenmiş verileri döndür
            $poll->refresh();
            $poll->load('options');

            return $this->formatPollData($poll, $user);
        });
    }

    /**
     * Anket verisini yüzdelik dilimleriyle ve gizlilik kurallarıyla biçimlendirir.
     * Oy kullanmayan kullanıcılardan anket bitene kadar yüzdelikler saklanır.
     *
     * @return array<string, mixed>
     */
    public function formatPollData(Poll $poll, ?User $user = null): array
    {
        if (! $poll->relationLoaded('options')) {
            $poll->load('options');
        }

        /** @var PollVote|null $userVote */
        $userVote = $user
            ? PollVote::query()->where('poll_id', $poll->id)->where('user_id', $user->id)->first()
            : null;

        $hasVoted = $userVote !== null;
        $isExpired = $poll->isExpired();
        $showResults = $hasVoted || $isExpired;

        $options = $poll->options->map(function (PollOption $opt) use ($poll, $showResults, $userVote) {
            $percentage = $poll->total_votes > 0
                ? round(((float) $opt->votes_count / (float) $poll->total_votes) * 100, 1)
                : 0.0;

            return [
                'id' => $opt->id,
                'option_text' => $opt->option_text,
                'votes_count' => $showResults ? (int) $opt->votes_count : null,
                'percentage' => $showResults ? $percentage : null,
                'is_user_vote' => $userVote?->poll_option_id === $opt->id,
            ];
        });

        // Kazanan seçeneğin tespiti
        $winnerOptionId = null;
        if ($showResults && $poll->total_votes > 0) {
            $winner = $poll->options->sortByDesc('votes_count')->first();
            $winnerOptionId = $winner?->id;
        }

        return [
            'id' => $poll->id,
            'question' => $poll->question,
            'expires_at' => $poll->expires_at->toIso8601String(),
            'expires_at_human' => $poll->expires_at->diffForHumans(),
            'is_expired' => $isExpired,
            'total_votes' => (int) $poll->total_votes,
            'has_voted' => $hasVoted,
            'show_results' => $showResults,
            'user_voted_option_id' => $userVote?->poll_option_id,
            'winner_option_id' => $winnerOptionId,
            'options' => $options->values()->toArray(),
            'card_url' => url("/polls/{$poll->id}/card"),
        ];
    }

    /**
     * Anket sonuçlarını X, WhatsApp ve basında paylaşılabilecek yüksek çözünürlüklü
     * (1200x630 PNG) şık bir infografik kartına dönüştürür.
     */
    public function generateInfographicCard(int $pollId): string
    {
        /** @var Poll $poll */
        $poll = Poll::query()
            ->with(['options', 'post.location', 'post.user'])
            ->findOrFail($pollId);

        $width = 1200;
        $height = 630;

        $image = imagecreatetruecolor($width, $height);

        // Renk paleti (Koyu tema & Neon modern vurgular)
        $bgDark      = imagecolorallocate($image, 11, 15, 25);    // #0b0f19
        $cardBg      = imagecolorallocate($image, 22, 29, 45);    // #161d2d
        $borderCol   = imagecolorallocate($image, 42, 53, 79);    // #2a354f
        $textWhite   = imagecolorallocate($image, 255, 255, 255);
        $textSlate   = imagecolorallocate($image, 156, 163, 175); // #9ca3af
        $blueBrand   = imagecolorallocate($image, 59, 130, 246);  // #3b82f6
        $emeraldWin  = imagecolorallocate($image, 16, 185, 129);  // #10b981
        $barBg       = imagecolorallocate($image, 31, 41, 64);    // #1f2940
        $badgeBg     = imagecolorallocate($image, 37, 99, 235);   // #2563eb

        // 1. Arka Planı Doldur
        imagefilledrectangle($image, 0, 0, $width, $height, $bgDark);

        // 2. Ana Kart Alanı ve İnce Çerçeve
        imagefilledrectangle($image, 50, 40, $width - 50, $height - 40, $cardBg);
        imagerectangle($image, 50, 40, $width - 50, $height - 40, $borderCol);

        // 3. Üst Bilgi Barı (Marka + Konum Rozeti)
        imagestring($image, 5, 80, 70, "M E Y D A N  |  Y E R E L   A N K E T", $blueBrand);

        $locationName = $poll->post?->location
            ? "📍 " . $poll->post->location->city . " / " . $poll->post->location->district
            : "🌍 Genel Meydan Akisi";
        
        // Konum Rozeti Kutusu
        imagefilledrectangle($image, 780, 65, 1120, 105, $badgeBg);
        imagestring($image, 4, 800, 77, $locationName, $textWhite);

        // 4. Anket Sorusu (Geniş font & satır sarma)
        $question = $poll->question;
        $wrappedQuestion = explode("\n", wordwrap($question, 62, "\n"));
        $y = 135;
        foreach (array_slice($wrappedQuestion, 0, 2) as $qLine) {
            imagestring($image, 5, 80, $y, $qLine, $textWhite);
            $y += 30;
        }

        // 5. Seçenekler ve Yüzdelik Barları
        $totalVotes = max(1, (int) $poll->total_votes);
        $options = $poll->options->sortBy('id')->values();
        $optionStartY = max(215, $y + 15);
        $optionHeight = 65;

        // En çok oy alan seçeneği bul
        $maxVotes = $poll->options->max('votes_count') ?? 0;

        foreach ($options as $idx => $opt) {
            $optY = $optionStartY + ($idx * ($optionHeight + 12));
            if ($optY + $optionHeight > 540) {
                break;
            }

            $percentage = round(((float) $opt->votes_count / (float) $totalVotes) * 100, 1);
            $isWinner = $poll->total_votes > 0 && $opt->votes_count === $maxVotes && $maxVotes > 0;

            // Seçenek Çerçevesi / Arka Planı
            imagefilledrectangle($image, 80, $optY, 1120, $optY + $optionHeight, $barBg);
            imagerectangle($image, 80, $optY, 1120, $optY + $optionHeight, $borderCol);

            // İlerleme Barı (Dolgu)
            $barWidth = (int) (1040 * ($percentage / 100));
            if ($barWidth > 0) {
                $fillColor = $isWinner ? $emeraldWin : $blueBrand;
                imagefilledrectangle($image, 80, $optY, 80 + $barWidth, $optY + $optionHeight, $fillColor);
            }

            // Seçenek Metni
            $optText = (string) $opt->option_text;
            if (mb_strlen($optText, 'UTF-8') > 45) {
                $optText = mb_substr($optText, 0, 42, 'UTF-8') . '...';
            }
            imagestring($image, 5, 100, $optY + 22, $optText, $textWhite);

            // Yüzde ve Oy Sayısı
            $statsString = "%{$percentage} ({$opt->votes_count} Oy)";
            if ($isWinner && $poll->total_votes > 0) {
                $statsString .= "  [KAZANAN]";
            }
            imagestring($image, 5, 900, $optY + 22, $statsString, $textWhite);
        }

        // 6. Alt Bilgi: Toplam Oy, Durum ve Davet
        $statusText = $poll->isExpired() ? "Oylama Sonuclandi" : "Oylama Devam Ediyor";
        $footerText = "Toplam {$poll->total_votes} yerel oy kullanildi  •  {$statusText}";
        imagestring($image, 4, 80, 565, $footerText, $textSlate);
        imagestring($image, 4, 830, 565, "meydan.com'da oy ver ve tartis ->", $blueBrand);

        ob_start();
        imagepng($image);
        $pngData = (string) ob_get_clean();
        imagedestroy($image);

        return $pngData;
    }
}

