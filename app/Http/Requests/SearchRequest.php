<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ArticleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * SearchRequest
 *
 * Validates article search requests.
 */
class SearchRequest extends FormRequest
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
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'author_id' => ['sometimes', 'string'],
            'status' => ['sometimes', Rule::in(ArticleStatus::values())],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string', 'max:50'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['string', 'max:50'],
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date', 'after_or_equal:date_from'],
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
            'q.min' => 'The search term must be at least 2 characters.',
            'q.max' => 'The search term cannot exceed 255 characters.',
            'per_page.integer' => 'The number of items per page must be an integer.',
            'per_page.min' => 'The minimum number of items per page is 1.',
            'per_page.max' => 'The maximum number of items per page is 100.',
            'status.in' => 'The provided status is invalid.',
            'tags.array' => 'Tags must be sent as an array.',
            'tags.*.string' => 'Each tag must be a string.',
            'tags.*.max' => 'Each tag cannot exceed 50 characters.',
            'categories.array' => 'Categories must be sent as an array.',
            'categories.*.string' => 'Each category must be a string.',
            'categories.*.max' => 'Each category cannot exceed 50 characters.',
            'date_from.date' => 'The start date must be a valid date.',
            'date_to.date' => 'The end date must be a valid date.',
            'date_to.after_or_equal' => 'The end date must be equal to or after the start date.',
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
     * Get the filters array.
     *
     * @return array<string, mixed>
     */
    public function getFilters(): array
    {
        /** @var array<string, mixed> $filters */
        $filters = $this->only([
            'author_id',
            'status',
            'tags',
            'categories',
            'date_from',
            'date_to',
        ]);

        return $filters;
    }

    /**
     * Get the per page value.
     */
    public function getPerPage(): int
    {
        $perPage = $this->input('per_page', 15);

        if (is_int($perPage)) {
            return $perPage;
        }

        if (is_numeric($perPage)) {
            return (int) $perPage;
        }

        return 15;
    }
}
