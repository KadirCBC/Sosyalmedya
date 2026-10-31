<?php

declare(strict_types=1);

namespace App\Http\Requests\Moderation;

use App\Enums\ReportReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateReportRequest extends FormRequest
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
            'reportable_type' => [
                'required',
                'string',
                Rule::in(['post', 'user']),
            ],
            'reportable_id' => [
                'required',
                'integer',
            ],
            'reason' => [
                'required',
                Rule::enum(ReportReason::class),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reportable_type.required' => 'Şikayet edilen tür belirtilmelidir.',
            'reportable_type.in' => 'Geçersiz şikayet türü. (post veya user olmalıdır)',
            'reportable_id.required' => 'Şikayet edilen içerik veya kullanıcı ID zorunludur.',
            'reason.required' => 'Şikayet nedeni seçilmelidir.',
            'reason.enum' => 'Geçersiz şikayet nedeni.',
            'description.max' => 'Açıklama en fazla 1000 karakter olabilir.',
        ];
    }
}

