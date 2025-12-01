<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ArticleStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * SearchRequest
 *
 * Valida requisições de busca de artigos.
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
            'q.required' => 'O termo de busca é obrigatório.',
            'q.min' => 'O termo de busca deve ter pelo menos 2 caracteres.',
            'q.max' => 'O termo de busca não pode exceder 255 caracteres.',
            'per_page.integer' => 'O número de itens por página deve ser um número inteiro.',
            'per_page.min' => 'O número mínimo de itens por página é 1.',
            'per_page.max' => 'O número máximo de itens por página é 100.',
            'status.in' => 'O status informado é inválido.',
            'tags.array' => 'As tags devem ser enviadas como um array.',
            'tags.*.string' => 'Cada tag deve ser uma string.',
            'tags.*.max' => 'Cada tag não pode exceder 50 caracteres.',
            'categories.array' => 'As categorias devem ser enviadas como um array.',
            'categories.*.string' => 'Cada categoria deve ser uma string.',
            'categories.*.max' => 'Cada categoria não pode exceder 50 caracteres.',
            'date_from.date' => 'A data inicial deve ser uma data válida.',
            'date_to.date' => 'A data final deve ser uma data válida.',
            'date_to.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
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
