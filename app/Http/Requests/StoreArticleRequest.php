<?php

namespace App\Http\Requests;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class StoreArticleRequest extends FormRequest
{
    private const string MAX_STRING_LENGTH = 'max:255';

    private const string MAX_TEXT_LENGTH = 'max:500';

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
            'title' => ['required', 'string', self::MAX_STRING_LENGTH],
            'slug' => ['nullable', 'string', self::MAX_STRING_LENGTH, 'unique:articles,slug'],
            'content' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', self::MAX_TEXT_LENGTH],
            'status' => ['required', 'string', Rule::in(array_column(ArticleStatus::cases(), 'value'))],
            'type' => ['required', 'string', Rule::in(array_column(ArticleType::cases(), 'value'))],
            'featured_image' => ['nullable', 'url'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['string', 'max:50'],
            'meta_data' => ['nullable', 'array'],
            'is_featured' => ['nullable', 'boolean'],
            'is_pinned' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', self::MAX_STRING_LENGTH],
            'seo_description' => ['nullable', 'string', self::MAX_TEXT_LENGTH],
            'seo_keywords' => ['nullable', 'string', self::MAX_STRING_LENGTH],
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
            'title.required' => 'O título do artigo é obrigatório.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'content.required' => 'O conteúdo do artigo é obrigatório.',
            'status.required' => 'O status do artigo é obrigatório.',
            'status.in' => 'O status do artigo é inválido.',
            'type.required' => 'O tipo do artigo é obrigatório.',
            'type.in' => 'O tipo do artigo é inválido.',
            'featured_image.url' => 'A imagem destacada deve ser uma URL válida.',
            'tags.array' => 'As tags devem ser um array.',
            'categories.array' => 'As categorias devem ser um array.',
            'published_at.date' => 'A data de publicação deve ser uma data válida.',
        ];
    }
}
