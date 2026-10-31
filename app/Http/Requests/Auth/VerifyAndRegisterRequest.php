<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Support\PhoneHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class VerifyAndRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Gelen telefon numarasını validasyondan önce normalize eder.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone_normalized' => PhoneHelper::normalize((string) $this->input('phone')),
            ]);
        }
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^(?:\+?90|0)?5[0-9]{9}$/',
            ],
            'phone_normalized' => [
                'required',
                'string',
                Rule::unique('users', 'phone'),
            ],
            'code' => [
                'required',
                'string',
                'size:6',
                'regex:/^[0-9]{6}$/',
            ],
            'name' => [
                'required',
                'string',
                'max:60',
            ],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'alpha_dash',
                Rule::unique('users', 'username'),
            ],
            'password' => [
                'required',
                'string',
                Password::min(8),
            ],
            'location_id' => [
                'required',
                'integer',
                Rule::exists('locations', 'id'),
            ],
            'avatar_path' => [
                'nullable',
                'string',
                'max:255',
            ],
            'bio' => [
                'nullable',
                'string',
                'max:160',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Telefon numarası zorunludur.',
            'phone.regex' => 'Lütfen geçerli bir Türkiye cep telefonu numarası giriniz.',
            'phone_normalized.unique' => 'Bu telefon numarası ile kayıtlı bir kullanıcı zaten bulunmaktadır.',
            'code.required' => '6 haneli SMS doğrulama kodu zorunludur.',
            'code.size' => 'Doğrulama kodu 6 haneli olmalıdır.',
            'code.regex' => 'Doğrulama kodu yalnızca rakamlardan oluşmalıdır.',
            'name.required' => 'Ad Soyad alanı zorunludur.',
            'name.max' => 'Ad Soyad en fazla 60 karakter olabilir.',
            'username.required' => 'Kullanıcı adı zorunludur.',
            'username.unique' => 'Bu kullanıcı adı zaten alınmış.',
            'username.alpha_dash' => 'Kullanıcı adı yalnızca harf, rakam, tire ve alt çizgi içerebilir.',
            'password.required' => 'Şifre alanı zorunludur.',
            'password.min' => 'Şifre en az 8 karakterden oluşmalıdır.',
            'location_id.required' => 'Lokasyon seçimi zorunludur.',
            'location_id.exists' => 'Seçilen lokasyon geçerli değil.',
            'bio.max' => 'Biyografi en fazla 160 karakter olabilir.',
        ];
    }
}

