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
    #[\Override]
    public function messages(): array
    {
        return [
            'name.min' => 'The name must be at least 3 characters.',
            'name.max' => 'The name cannot exceed 255 characters.',
            'username.min' => 'The username must be at least 3 characters.',
            'username.max' => 'The username cannot exceed 50 characters.',
            'username.regex' => 'The username may only contain letters, numbers, and underscores.',
            'username.unique' => 'This username is already in use.',
            'bio.max' => 'The bio cannot exceed 500 characters.',
            'avatar_url.url' => 'The avatar URL must be valid.',
            'avatar_url.max' => 'The avatar URL cannot exceed 500 characters.',
        ];
    }
}
