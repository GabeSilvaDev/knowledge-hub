<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * AutocompleteRequest
 *
 * Valida requisições de autocomplete de busca.
 */
class AutocompleteRequest extends FormRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2', 'max:255'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:20'],
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
            'q.required' => 'O termo de busca é obrigatório.',
            'q.string' => 'O termo de busca deve ser uma string.',
            'q.min' => 'O termo de busca deve ter pelo menos 2 caracteres.',
            'q.max' => 'O termo de busca não pode exceder 255 caracteres.',
            'limit.integer' => 'O limite deve ser um número inteiro.',
            'limit.min' => 'O limite mínimo é 1.',
            'limit.max' => 'O limite máximo é 20.',
        ];
    }

    /**
     * Get the search query.
     */
    public function getQuery(): string
    {
        $query = $this->input('q');

        return is_string($query) ? $query : '';
    }

    /**
     * Get the limit value.
     */
    public function getLimit(): int
    {
        $limit = $this->input('limit', 10);

        if (is_int($limit)) {
            return $limit;
        }

        if (is_numeric($limit)) {
            return (int) $limit;
        }

        return 10;
    }
}
