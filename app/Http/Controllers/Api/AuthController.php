<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\LoginWithOtpRequest;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyAndRegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Kullanıcının telefonuna SMS OTP gönderir.
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $result = $this->authService->sendOtp((string) $request->validated('phone'));

        return response()->json($result, Response::HTTP_OK);
    }

    /**
     * OTP kodunu onaylayarak kullanıcı kaydını tamamlar ve Sanctum token döner.
     */
    public function register(VerifyAndRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->verifyOtpAndRegister($request->validated());

        return response()->json([
            'message' => 'Kayıt işlemi başarıyla tamamlandı.',
            'user' => $result['user'],
            'token' => $result['token'],
        ], Response::HTTP_CREATED);
    }

    /**
     * Telefon numarası ve şifre ile giriş yapar.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginWithPhone(
            (string) $request->validated('phone'),
            (string) $request->validated('password')
        );

        return response()->json([
            'message' => 'Giriş başarılı.',
            'user' => $result['user'],
            'token' => $result['token'],
        ], Response::HTTP_OK);
    }

    /**
     * Şifresiz, doğrudan SMS OTP ile giriş yapar.
     */
    public function loginWithOtp(LoginWithOtpRequest $request): JsonResponse
    {
        $result = $this->authService->loginWithOtp(
            (string) $request->validated('phone'),
            (string) $request->validated('code')
        );

        return response()->json([
            'message' => 'Giriş başarılı.',
            'user' => $result['user'],
            'token' => $result['token'],
        ], Response::HTTP_OK);
    }

    /**
     * Giriş yapan kullanıcının mevcut API token'ını iptal eder (çıkış yapar).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Başarıyla çıkış yapıldı.',
        ], Response::HTTP_OK);
    }

    /**
     * Kimliği doğrulanmış kullanıcı bilgilerini ve bağlı olduğu lokasyonu döner.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->load('location'),
        ], Response::HTTP_OK);
    }
}

