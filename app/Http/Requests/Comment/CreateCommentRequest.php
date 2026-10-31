<?php

declare(strict_types=1);

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCommentRequest extends FormRequest
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
        $postId = (int) $this->route('post_id');

        return [
            'content' => [
                'required',
                'string',
                'max:300',
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('comments', 'id')->where(function ($query) use ($postId) {
                    $query->where('post_id', $postId);
                }),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Yorum içeriği boş bırakılamaz.',
            'content.max' => 'Yorum içeriği en fazla 300 karakter olabilir.',
            'parent_id.exists' => 'Yanıtlanan yorum bu gönderiye ait değil veya mevcut değil.',
        ];
    }
}

