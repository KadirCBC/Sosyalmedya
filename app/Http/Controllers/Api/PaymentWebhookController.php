<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Ad\AdWizardService;
use App\Services\Audit\ActivityLogService;
use App\Services\Payment\PayTrPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        protected PayTrPaymentService $payTrPaymentService,
        protected AdWizardService $adWizardService,
        protected ActivityLogService $activityLogService
    ) {}

    /**
     * PayTR / iyzico Webhook Bildirim Uç Noktası.
     * Ödeme tamamlandığında PayTR sunucuları doğrudan bu adresi POST ile tetikler.
     */
    public function payTrCallback(Request $request): Response
    {
        $postData = $request->all();

        // 1. PayTR Hash Güvenlik Doğrulaması
        if (! $this->payTrPaymentService->validateCallbackHash($postData)) {
            Log::warning('Geçersiz PayTR Webhook İmzası', $postData);
            return response('PAYTR notification failed: bad hash', 400);
        }

        $merchantOid = (string) ($postData['merchant_oid'] ?? '');
        $status = (string) ($postData['status'] ?? '');
        $totalAmount = ((float) ($postData['total_amount'] ?? 0)) / 100; // Kuruş -> TL

        if ($status === 'success') {
            // Reklamı anında aktifleştir ve esnafa SMS at
            $ad = $this->adWizardService->activateAdAfterPayment($merchantOid, $totalAmount);

            // 5651 Sayılı Kanun mali işlem logu
            if ($ad) {
                $this->activityLogService->log(
                    request: $request,
                    action: 'ad_payment_success',
                    user: $ad->user,
                    content: "PayTR Ödeme Başarılı: {$totalAmount} TL - {$merchantOid}",
                    targetType: \App\Models\Ad::class,
                    targetId: $ad->id
                );
            }
        } else {
            Log::info("PayTR Ödeme Başarısız: {$merchantOid} - Hata: " . ($postData['failed_reason_msg'] ?? ''));
        }

        // PayTR dokümantasyonuna göre başarılı bildirim alındığında "OK" dönülmelidir
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }
}

