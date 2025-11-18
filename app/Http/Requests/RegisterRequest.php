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
            'name.required' => 'O nome é obrigatório.',
            'name.min' => 'O nome deve ter no mínimo 3 caracteres.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ser um endereço válido.',
            'email.unique' => 'Este email já está em uso.',
            'username.required' => 'O nome de usuário é obrigatório.',
            'username.min' => 'O nome de usuário deve ter no mínimo 3 caracteres.',
            'username.unique' => 'Este nome de usuário já está em uso.',
            'username.alpha_dash' => 'O nome de usuário pode conter apenas letras, números, hífens e underscores.',
            'password.required' => 'A senha é obrigatória.',
            'password.confirmed' => 'A confirmação de senha não confere.',
            'password.regex' => 'A senha deve conter pelo menos uma letra maiúscula.',
            'bio.max' => 'A biografia não pode ter mais de 500 caracteres.',
            'avatar_url.url' => 'A URL do avatar deve ser válida.',
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
            'name' => 'nome',
            'email' => 'email',
            'username' => 'nome de usuário',
            'password' => 'senha',
            'bio' => 'biografia',
            'avatar_url' => 'URL do avatar',
        ];
    }
}
