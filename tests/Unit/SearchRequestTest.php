<?php

use App\Enums\ArticleStatus;
use App\Http\Requests\SearchRequest;
use Illuminate\Support\Facades\Validator;

describe('SearchRequest Unit Tests', function (): void {
    it('authorizes all requests', function (): void {
        $request = new SearchRequest;

        expect($request->authorize())->toBeTrue();
    });

    it('validates required query parameter', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make([], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('q'))->toBeTrue();
    });

    it('validates query min length', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => 'a'], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('q'))->toBeTrue();
    });

    it('validates query max length', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => str_repeat('a', 256)], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('q'))->toBeTrue();
    });

    it('validates per_page must be integer', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => 'test', 'per_page' => 'invalid'], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('per_page'))->toBeTrue();
    });

    it('validates per_page min value', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => 'test', 'per_page' => 0], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('per_page'))->toBeTrue();
    });

    it('validates per_page max value', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => 'test', 'per_page' => 101], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('per_page'))->toBeTrue();
    });

    it('validates status must be valid enum value', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => 'test', 'status' => 'invalid'], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('status'))->toBeTrue();
    });

    it('accepts valid status enum value', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => 'test', 'status' => ArticleStatus::PUBLISHED->value], $request->rules());

        expect($validator->passes())->toBeTrue();
    });

    it('validates tags must be array', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => 'test', 'tags' => 'invalid'], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('tags'))->toBeTrue();
    });

    it('validates tag items max length', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => 'test', 'tags' => [str_repeat('a', 51)]], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('tags.0'))->toBeTrue();
    });

    it('validates categories must be array', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => 'test', 'categories' => 'invalid'], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('categories'))->toBeTrue();
    });

    it('validates category items max length', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => 'test', 'categories' => [str_repeat('a', 51)]], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('categories.0'))->toBeTrue();
    });

    it('validates date_from must be valid date', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make(['q' => 'test', 'date_from' => 'invalid'], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('date_from'))->toBeTrue();
    });

    it('validates date_to must be after or equal date_from', function (): void {
        $request = new SearchRequest;

        $validator = Validator::make([
            'q' => 'test',
            'date_from' => '2024-01-10',
            'date_to' => '2024-01-05',
        ], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('date_to'))->toBeTrue();
    });

    it('returns query string', function (): void {
        $request = SearchRequest::create('/search', 'GET', ['q' => 'laravel']);

        expect($request->getQuery())->toBe('laravel');
    });

    it('returns empty string when query is not string', function (): void {
        $request = SearchRequest::create('/search', 'GET', ['q' => ['array']]);

        expect($request->getQuery())->toBe('');
    });

    it('returns filters array', function (): void {
        $request = SearchRequest::create('/search', 'GET', [
            'q' => 'test',
            'author_id' => '123',
            'status' => 'published',
            'tags' => ['laravel'],
        ]);

        $filters = $request->getFilters();

        expect($filters)->toHaveKeys(['author_id', 'status', 'tags']);
    });

    it('returns per page value', function (): void {
        $request = SearchRequest::create('/search', 'GET', ['q' => 'test', 'per_page' => 20]);

        expect($request->getPerPage())->toBe(20);
    });

    it('returns default per page value when not provided', function (): void {
        $request = SearchRequest::create('/search', 'GET', ['q' => 'test']);

        expect($request->getPerPage())->toBe(15);
    });

    it('returns default per page when value is not numeric', function (): void {
        $request = SearchRequest::create('/search', 'GET', ['q' => 'test', 'per_page' => 'invalid']);

        expect($request->getPerPage())->toBe(15);
    });

    it('converts numeric string to integer for per page', function (): void {
        $request = SearchRequest::create('/search', 'GET', ['q' => 'test', 'per_page' => '25']);

        expect($request->getPerPage())->toBe(25);
    });
});
