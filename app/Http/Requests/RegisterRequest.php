<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Override;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    private const string MAX_255 = 'max:255';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', self::MAX_255],
            'email' => ['required', 'string', 'email', self::MAX_255, Rule::unique('users', 'email')],
            'username' => ['required', 'string', 'min:3', self::MAX_255, Rule::unique('users', 'username'), 'alpha_dash'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers(), 'regex:/[A-Z]/'],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar_url' => ['nullable', 'string', 'url', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    #[Override()]
    public function messages(): array
    {
        return [
            'name.required' => 'The name is required.',
            'name.min' => 'The name must be at least 3 characters.',
            'name.max' => 'The name cannot exceed 255 characters.',
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid address.',
            'email.unique' => 'This email is already in use.',
            'username.required' => 'The username is required.',
            'username.min' => 'The username must be at least 3 characters.',
            'username.unique' => 'This username is already in use.',
            'username.alpha_dash' => 'The username may only contain letters, numbers, dashes, and underscores.',
            'password.required' => 'The password is required.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.regex' => 'The password must contain at least one uppercase letter.',
            'bio.max' => 'The bio cannot exceed 500 characters.',
            'avatar_url.url' => 'The avatar URL must be valid.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    #[Override()]
    public function attributes(): array
    {
        return [
            'name' => 'name',
            'email' => 'email',
            'username' => 'username',
            'password' => 'password',
            'bio' => 'bio',
            'avatar_url' => 'avatar URL',
        ];
    }
}
