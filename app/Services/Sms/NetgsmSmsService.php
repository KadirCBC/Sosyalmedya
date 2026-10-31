<?php

declare(strict_types=1);

namespace App\Services\Sms;

use App\Contracts\SmsServiceInterface;
use App\Support\PhoneHelper;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class NetgsmSmsService implements SmsServiceInterface
{
    protected string $usercode;
    protected string $password;
    protected string $header;
    protected string $endpoint;
    protected int $timeout;

    public function __construct()
    {
        $this->usercode = (string) config('sms.drivers.netgsm.usercode');
        $this->password = (string) config('sms.drivers.netgsm.password');
        $this->header = (string) config('sms.drivers.netgsm.header');
        $this->endpoint = (string) config('sms.drivers.netgsm.endpoint', 'https://api.netgsm.com.tr/sms/send/xml');
        $this->timeout = (int) config('sms.drivers.netgsm.timeout', 10);
    }

    /**
     * Netgsm XML API kullanarak SMS gönderimi yapar.
     */
    public function send(string $phone, string $message): bool
    {
        $normalizedPhone = PhoneHelper::normalize($phone);

        // Netgsm XML gövdesi oluşturma
        $xmlPayload = $this->buildXmlPayload($normalizedPhone, $message);

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/xml; charset=utf-8',
                ])
                ->send('POST', $this->endpoint, [
                    'body' => $xmlPayload,
                ]);

            if (! $response->successful()) {
                Log::error('Netgsm SMS HTTP Hatası', [
                    'status' => $response->status(),
                    'phone' => $normalizedPhone,
                    'response' => $response->body(),
                ]);
                return false;
            }

            $responseBody = trim($response->body());
            $responseCode = substr($responseBody, 0, 2);

            // 00, 01, 02 kodları Netgsm tarafında başarılı gönderim/kabul anlamına gelir
            if (in_array($responseCode, ['00', '01', '02'], true)) {
                Log::info('Netgsm SMS başarıyla gönderildi', [
                    'phone' => $normalizedPhone,
                    'job_id' => $responseBody,
                ]);
                return true;
            }

            Log::error('Netgsm SMS Sağlayıcı Hatası', [
                'code' => $responseCode,
                'response' => $responseBody,
                'phone' => $normalizedPhone,
            ]);

            return false;
        } catch (Throwable $e) {
            Log::critical('Netgsm SMS Gönderim İstisnası', [
                'phone' => $normalizedPhone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Netgsm 1:N SMS XML şablonunu üretir.
     */
    protected function buildXmlPayload(string $phone, string $message): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<mainbody>
    <header>
        <company dil="TR">Netgsm</company>
        <usercode>{$this->usercode}</usercode>
        <password>{$this->password}</password>
        <type>1:n</type>
        <msgheader>{$this->header}</msgheader>
    </header>
    <body>
        <msg><![CDATA[{$message}]]></msg>
        <no>{$phone}</no>
    </body>
</mainbody>
XML;
    }
}

