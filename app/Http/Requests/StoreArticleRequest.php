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
            'title.required' => 'The article title is required.',
            'title.max' => 'The title cannot exceed 255 characters.',
            'content.required' => 'The article content is required.',
            'status.required' => 'The article status is required.',
            'status.in' => 'The article status is invalid.',
            'type.required' => 'The article type is required.',
            'type.in' => 'The article type is invalid.',
            'featured_image.url' => 'The featured image must be a valid URL.',
            'tags.array' => 'Tags must be an array.',
            'categories.array' => 'Categories must be an array.',
            'published_at.date' => 'The publication date must be a valid date.',
        ];
    }
}
