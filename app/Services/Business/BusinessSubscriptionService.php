<?php

declare(strict_types=1);

namespace App\Services\Business;

use App\Contracts\SmsServiceInterface;
use App\Models\BusinessProfile;
use App\Models\User;
use App\Services\Payment\PayTrPaymentService;
use App\Services\Reputation\ReputationService;
use Illuminate\Support\Facades\Log;

class BusinessSubscriptionService
{
    /**
     * Aylık sabit "Doğrulanmış Esnaf Paketi" abonelik bedeli (TL).
     */
    public const MONTHLY_SUBSCRIPTION_PRICE = 499.00;

    public function __construct(
        protected PayTrPaymentService $payTrService,
        protected ReputationService $reputationService,
        protected SmsServiceInterface $smsService
    ) {}

    /**
     * Esnaf için PayTR ödeme oturumu başlatır.
     *
     * @return array{success: bool, iframe_url: string, error?: string}
     */
    public function createSubscriptionSession(BusinessProfile $profile, User $user, string $clientIp): array
    {
        // PayTR merchant_oid formatı: BIZ_SUB_{profile_id}_{timestamp}
        $merchantOid = 'BIZ_SUB_' . $profile->id . '_' . time();

        // PayTR token üretimi (Test/Mock mod desteği dahil)
        $userBasket = base64_encode(json_encode([
            ['Doğrulanmış Esnaf Aylık Abonelik Paketi', '499.00', 1],
        ]));

        $paymentAmount = (int) (self::MONTHLY_SUBSCRIPTION_PRICE * 100); // Kuruş cinsinden

        // Eğer PayTR servis anahtarları tanımlı değilse sandbox test linki dön
        if (config('services.paytr.merchant_id') === 'YOUR_PAYTR_MERCHANT_ID' || empty(config('services.paytr.merchant_id'))) {
            return [
                'success' => true,
                'iframe_url' => route('business.subscription.mock', ['id' => $profile->id]),
                'token' => 'SANDBOX_MOCK_TOKEN',
            ];
        }

        // Gerçek PayTR entegrasyonu
        return $this->payTrService->createSubscriptionToken($profile, $user, $merchantOid, $paymentAmount, $clientIp);
    }

    /**
     * Ödeme webhook teyidi geldiğinde aboneliği 30 günlüğüne aktif eder ve rozetleri açar.
     */
    public function activateSubscription(BusinessProfile $profile, float $paidAmount = self::MONTHLY_SUBSCRIPTION_PRICE): void
    {
        $profile->update([
            'is_verified' => true,
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addDays(30),
            'free_boosts_remaining' => 2, // Haftalık 2 ücretsiz öne çıkarma hakkı
        ]);

        $user = $profile->user;
        if ($user) {
            // Kullanıcı rolünü esnafa yükselt
            $user->update(['role' => 'merchant']);

            // İtibar rozetini "Mahalle Esnafı" olarak güncelle
            $this->reputationService->adjustPoints(
                $user->id,
                'merchant_subscription_activated',
                10,
                'Doğrulanmış Esnaf İşletme Paketi aktif edildi'
            );

            // Esnafa onay SMS'i gönder
            if ($user->phone) {
                $smsMessage = "Tebrikler {$profile->business_name}! Doğrulanmış Esnaf İşletme Paketiniz aktif edildi. Menünüz yayında ve haftalık 2 ücretsiz öne çıkarma hakkınız tanımlandı.";
                $this->smsService->sendSms($user->phone, $smsMessage);
            }
        }

        Log::info("Esnaf aboneliği aktif edildi", [
            'business_id' => $profile->id,
            'business_name' => $profile->business_name,
            'paid_amount' => $paidAmount,
        ]);
    }
}

