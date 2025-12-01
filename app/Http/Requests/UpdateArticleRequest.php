<?php

namespace App\Http\Requests;

use App\Enums\ArticleStatus;
use App\Enums\ArticleType;
use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

class UpdateArticleRequest extends FormRequest
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
        /** @var Article|null $article */
        $article = $this->route('article');
        $articleId = $article?->id;

        return [
            'title' => ['sometimes', 'string', self::MAX_STRING_LENGTH],
            'slug' => ['nullable', 'string', self::MAX_STRING_LENGTH, Rule::unique('articles', 'slug')->ignore($articleId)],
            'content' => ['sometimes', 'string'],
            'excerpt' => ['nullable', 'string', self::MAX_TEXT_LENGTH],
            'status' => ['sometimes', 'string', Rule::in(array_column(ArticleStatus::cases(), 'value'))],
            'type' => ['sometimes', 'string', Rule::in(array_column(ArticleType::cases(), 'value'))],
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
            'title.string' => 'The article title must be text.',
            'title.max' => 'The title cannot exceed 255 characters.',
            'content.string' => 'The article content must be text.',
            'status.in' => 'The article status is invalid.',
            'type.in' => 'The article type is invalid.',
            'featured_image.url' => 'The featured image must be a valid URL.',
            'tags.array' => 'Tags must be an array.',
            'categories.array' => 'Categories must be an array.',
            'published_at.date' => 'The publication date must be a valid date.',
        ];
    }
}
