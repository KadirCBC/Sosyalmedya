<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'password' => [
                'required',
                'string',
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
            'password.required' => 'Şifre zorunludur.',
        ];
    }
}

