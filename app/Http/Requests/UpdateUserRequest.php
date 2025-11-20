<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Update User Request.
 *
 * Validates data for updating user profile.
 */
class UpdateUserRequest extends FormRequest
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
        $userId = $this->user()?->id;

        return [
            'name' => ['sometimes', 'string', 'min:3', 'max:255'],
            'username' => [
                'sometimes',
                'string',
                'min:3',
                'max:50',
                'regex:/^[a-zA-Z0-9_]+$/',
                Rule::unique('users', 'username')->ignore($userId, '_id'),
            ],
            'bio' => ['sometimes', 'nullable', 'string', 'max:500'],
            'avatar_url' => ['sometimes', 'nullable', 'url', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'username.min' => 'O username deve ter pelo menos 3 caracteres.',
            'username.max' => 'O username não pode ter mais de 50 caracteres.',
            'username.regex' => 'O username pode conter apenas letras, números e underscores.',
            'username.unique' => 'Este username já está em uso.',
            'bio.max' => 'A bio não pode ter mais de 500 caracteres.',
            'avatar_url.url' => 'A URL do avatar deve ser válida.',
            'avatar_url.max' => 'A URL do avatar não pode ter mais de 500 caracteres.',
        ];
    }
}
