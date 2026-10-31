<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SmsServiceInterface;
use App\Jobs\SendSmsJob;
use App\Models\Otp;
use App\Models\User;
use App\Support\PhoneHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        protected SmsServiceInterface $smsService
    ) {}

    /**
     * 6 haneli OTP kodu üretir, veritabanına kaydeder ve SMS işini kuyruğa (Queue) ekler.
     * Rate-limiting: Aynı numaraya 60 saniye içinde yalnızca 1 kez kod gönderilebilir.
     *
     * @return array{message: string, expires_in: int}
     * @throws ValidationException
     */
    public function sendOtp(string $phone): array
    {
        $normalizedPhone = PhoneHelper::normalize($phone);

        // 1 dakikalık rate-limiting kontrolü (aynı numaraya 60 sn içinde tekrar kod gönderilemez)
        $hasRecentOtp = Otp::query()
            ->where('phone', $normalizedPhone)
            ->where('created_at', '>=', now()->subSeconds(60))
            ->exists();

        if ($hasRecentOtp) {
            throw ValidationException::withMessages([
                'phone' => ['Yeni bir doğrulama kodu istemeden önce lütfen 1 dakika bekleyin.'],
            ]);
        }

        // 6 haneli kriptografik olarak güvenli rastgele kod üretimi
        $code = (string) random_int(100000, 999999);

        // Önceki kullanılmamış kodları geçersiz kıl (güvenlik ve tekil kod garantisi)
        Otp::query()
            ->where('phone', $normalizedPhone)
            ->where('is_used', false)
            ->update(['is_used' => true]);

        // 3 dakika geçerlilik süresiyle yeni OTP kaydı
        Otp::query()->create([
            'phone' => $normalizedPhone,
            'code' => $code,
            'expires_at' => now()->addMinutes(3),
            'is_used' => false,
            'created_at' => now(),
        ]);

        // SMS gönderimini asenkron kuyruğa gönder (API anında 200 döner, cPanel kilitlenmez)
        $message = "Sosyal Medya onay kodunuz: {$code}. Güvenliğiniz için bu kodu kimseyle paylaşmayın. Süre: 3 dk.";
        SendSmsJob::dispatch($normalizedPhone, $message);

        return [
            'message' => 'Doğrulama kodu telefonunuza SMS olarak gönderildi.',
            'expires_in' => 180, // saniye
        ];
    }

    /**
     * OTP kodunu doğrular, kullanıcı kaydını açar ve Sanctum API token döner.
     *
     * @param array<string, mixed> $data
     * @return array{user: User, token: string}
     * @throws ValidationException
     */
    public function verifyOtpAndRegister(array $data): array
    {
        $normalizedPhone = PhoneHelper::normalize((string) $data['phone']);
        $code = (string) $data['code'];

        // OTP kontrolü
        /** @var Otp|null $otp */
        $otp = Otp::query()
            ->where('phone', $normalizedPhone)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages([
                'code' => ['Girilen doğrulama kodu geçersiz veya süresi dolmuş.'],
            ]);
        }

        return DB::transaction(function () use ($otp, $data, $normalizedPhone) {
            // OTP'yi kullanıldı olarak işaretle
            $otp->update(['is_used' => true]);

            // Kullanıcıyı oluştur
            $user = User::query()->create([
                'name' => $data['name'],
                'username' => $data['username'],
                'phone' => $normalizedPhone,
                'phone_verified_at' => now(),
                'password' => Hash::make((string) $data['password']),
                'location_id' => $data['location_id'],
                'avatar_path' => $data['avatar_path'] ?? null,
                'bio' => $data['bio'] ?? null,
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user->load('location'),
                'token' => $token,
            ];
        });
    }

    /**
     * Telefon numarası ve şifre ile giriş.
     *
     * @return array{user: User, token: string}
     * @throws ValidationException
     */
    public function loginWithPhone(string $phone, string $password): array
    {
        $normalizedPhone = PhoneHelper::normalize($phone);

        /** @var User|null $user */
        $user = User::query()
            ->where('phone', $normalizedPhone)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Telefon numarası veya şifre hatalı.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user->load('location'),
            'token' => $token,
        ];
    }

    /**
     * Şifresiz, doğrudan SMS OTP ile giriş.
     *
     * @return array{user: User, token: string}
     * @throws ValidationException
     */
    public function loginWithOtp(string $phone, string $code): array
    {
        $normalizedPhone = PhoneHelper::normalize($phone);

        /** @var User|null $user */
        $user = User::query()
            ->where('phone', $normalizedPhone)
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'phone' => ['Bu telefon numarasına ait kayıtlı bir kullanıcı bulunamadı.'],
            ]);
        }

        /** @var Otp|null $otp */
        $otp = Otp::query()
            ->where('phone', $normalizedPhone)
            ->where('code', $code)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otp) {
            throw ValidationException::withMessages([
                'code' => ['Girilen doğrulama kodu geçersiz veya süresi dolmuş.'],
            ]);
        }

        return DB::transaction(function () use ($otp, $user) {
            $otp->update(['is_used' => true]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user->load('location'),
                'token' => $token,
            ];
        });
    }
}
