<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

/**
 * Update Comment Request.
 *
 * Validates data for updating an existing comment.
 */
class UpdateCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:1', 'max:5000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    #[Override]
    public function messages(): array
    {
        return [
            'content.required' => 'O conteúdo do comentário é obrigatório.',
            'content.min' => 'O comentário deve ter pelo menos 1 caractere.',
            'content.max' => 'O comentário não pode ter mais de 5000 caracteres.',
        ];
    }
}
