<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * AutocompleteRequest
 *
 * Validates autocomplete search requests.
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
            'q.required' => 'The search term is required.',
            'q.string' => 'The search term must be a string.',
            'q.min' => 'The search term must be at least 2 characters.',
            'q.max' => 'The search term cannot exceed 255 characters.',
            'limit.integer' => 'The limit must be an integer.',
            'limit.min' => 'The minimum limit is 1.',
            'limit.max' => 'The maximum limit is 20.',
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
