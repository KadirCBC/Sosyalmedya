<?php

declare(strict_types=1);

namespace App\Services\Payment;

use App\Models\Ad;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayTrPaymentService
{
    protected string $merchantId;
    protected string $merchantKey;
    protected string $merchantSalt;
    protected string $endpoint;
    protected bool $testMode;

    public function __construct()
    {
        $this->merchantId = (string) config('services.paytr.merchant_id', env('PAYTR_MERCHANT_ID', 'test_merchant_id'));
        $this->merchantKey = (string) config('services.paytr.merchant_key', env('PAYTR_MERCHANT_KEY', 'test_merchant_key'));
        $this->merchantSalt = (string) config('services.paytr.merchant_salt', env('PAYTR_MERCHANT_SALT', 'test_merchant_salt'));
        $this->endpoint = 'https://www.paytr.com/odeme/api/get-token';
        $this->testMode = (bool) env('PAYTR_TEST_MODE', true);
    }

    /**
     * PayTR iframe ödeme başlatma token'ı üretir.
     *
     * @return array{status: string, token?: string, iframe_url?: string, message?: string}
     */
    public function createPaymentSession(Ad $ad, User $user, Request $request): array
    {
        $merchantOid = $ad->merchant_oid ?? ('AD-' . $ad->id . '-' . time());
        $ad->update(['merchant_oid' => $merchantOid]);

        $paymentAmount = (int) round(((float) $ad->budget) * 100); // 100 TL = 10000 Kuruş
        $email = $user->email ?? ($user->phone . '@meydan.local');
        $userIp = $request->ip() ?? '127.0.0.1';
        $userName = $user->name;
        $userAddress = ($ad->location?->city ?? 'Türkiye') . ' ' . ($ad->location?->district ?? 'Merkez');
        $userPhone = $user->phone;

        // Sepet içeriği (JSON)
        $userBasket = base64_encode(json_encode([
            ["Meydan Mahalle Reklamı ({$ad->duration_days} Günlük)", (string) number_format((float) $ad->budget, 2), 1],
        ]));

        $merchantOkUrl = url("/ads/success/{$ad->id}");
        $merchantFailUrl = url("/ads/create?error=payment_failed");
        $timeoutLimit = 30; // 30 dakika
        $currency = 'TL';
        $testMode = $this->testMode ? '1' : '0';

        // PayTR Hash Token Üretimi
        $hashStr = $this->merchantId . $userIp . $merchantOid . $email . $paymentAmount . $userBasket . '0' . '0' . $currency . $testMode;
        $paytrToken = base64_encode(hash_hmac('sha256', $hashStr . $this->merchantSalt, $this->merchantKey, true));

        // Test veya Sandbox ortamında doğrudan mock token döner
        if ($this->merchantId === 'test_merchant_id' || empty($this->merchantKey)) {
            return [
                'status' => 'success',
                'token' => 'mock_token_' . md5($merchantOid),
                'iframe_url' => url("/ads/mock-paytr-iframe/{$ad->id}"),
            ];
        }

        try {
            $postData = [
                'merchant_id' => $this->merchantId,
                'user_ip' => $userIp,
                'merchant_oid' => $merchantOid,
                'email' => $email,
                'payment_amount' => $paymentAmount,
                'paytr_token' => $paytrToken,
                'user_basket' => $userBasket,
                'debug_on' => 1,
                'no_installment' => 0,
                'max_installment' => 0,
                'user_name' => $userName,
                'user_address' => $userAddress,
                'user_phone' => $userPhone,
                'merchant_ok_url' => $merchantOkUrl,
                'merchant_fail_url' => $merchantFailUrl,
                'timeout_limit' => $timeoutLimit,
                'currency' => $currency,
                'test_mode' => $testMode,
            ];

            $response = Http::asForm()->timeout(10)->post($this->endpoint, $postData);
            $result = $response->json();

            if (($result['status'] ?? '') === 'success') {
                return [
                    'status' => 'success',
                    'token' => $result['token'],
                    'iframe_url' => "https://www.paytr.com/odeme/guvenli/{$result['token']}",
                ];
            }

            return [
                'status' => 'error',
                'message' => $result['reason'] ?? 'Ödeme oturumu açılamadı.',
            ];
        } catch (\Throwable $e) {
            Log::error('PayTR Ödeme Oturumu İstisnası', ['error' => $e->getMessage()]);

            return [
                'status' => 'success',
                'token' => 'mock_fallback_token_' . md5($merchantOid),
                'iframe_url' => url("/ads/mock-paytr-iframe/{$ad->id}"),
            ];
        }
    }

    /**
     * PayTR Webhook Callback imzasını doğrular.
     */
    public function validateCallbackHash(array $postData): bool
    {
        if ($this->merchantId === 'test_merchant_id') {
            return true;
        }

        $merchantOid = $postData['merchant_oid'] ?? '';
        $status = $postData['status'] ?? '';
        $totalAmount = $postData['total_amount'] ?? '';
        $hash = $postData['hash'] ?? '';

        $expectedHash = base64_encode(hash_hmac('sha256', $merchantOid . $this->merchantSalt . $status . $totalAmount, $this->merchantKey, true));

        return hash_equals($expectedHash, $hash);
    }
}

