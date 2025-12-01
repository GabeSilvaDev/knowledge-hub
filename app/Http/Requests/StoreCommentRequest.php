<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

/**
 * Store Comment Request.
 *
 * Validates data for creating a new comment.
 */
class StoreCommentRequest extends FormRequest
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
        return [
            'article_id' => ['required', 'string', 'exists:articles,_id'],
            'content' => ['required', 'string', 'min:1', 'max:5000'],
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
            'article_id.required' => 'The article ID is required.',
            'article_id.exists' => 'The specified article does not exist.',
            'content.required' => 'The comment content is required.',
            'content.min' => 'The comment must have at least 1 character.',
            'content.max' => 'The comment cannot exceed 5000 characters.',
        ];
    }
}
