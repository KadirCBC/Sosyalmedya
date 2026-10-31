<?php

declare(strict_types=1);

namespace App\Http\Requests\Post;

use App\Enums\PostScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePostRequest extends FormRequest
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
            'content' => [
                'required',
                'string',
                'max:500',
            ],
            'scope' => [
                'required',
                Rule::enum(PostScope::class),
            ],
            'location_id' => [
                'nullable',
                'integer',
                Rule::exists('locations', 'id'),
            ],
            'images' => [
                'nullable',
                'array',
                'max:4',
            ],
            'images.*' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:10240', // Maksimum 10MB
            ],
            'poll' => [
                'nullable',
                'array',
            ],
            'poll.question' => [
                'required_with:poll',
                'string',
                'max:255',
            ],
            'poll.options' => [
                'required_with:poll',
                'array',
                'min:2',
                'max:4',
            ],
            'poll.options.*' => [
                'required',
                'string',
                'max:100',
            ],
            'poll.duration_hours' => [
                'nullable',
                'integer',
                'min:1',
                'max:168',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Gönderi içeriği zorunludur.',
            'content.max' => 'Gönderi içeriği en fazla 500 karakter olabilir.',
            'scope.required' => 'Gönderi kapsamı (local veya general) seçilmelidir.',
            'scope.enum' => 'Geçersiz gönderi kapsamı. (local veya general olmalıdır)',
            'location_id.exists' => 'Belirtilen konum sistemde bulunamadı.',
            'images.max' => 'Bir gönderiye en fazla 4 adet görsel eklenebilir.',
            'images.*.image' => 'Yüklenen dosya geçerli bir görsel olmalıdır.',
            'images.*.mimes' => 'Görseller yalnızca JPEG, PNG veya WebP formatında olabilir.',
            'images.*.max' => 'Her bir görsel en fazla 10MB boyutunda olabilir.',
        ];
    }
}

