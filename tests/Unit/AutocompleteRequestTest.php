<?php

use App\Http\Requests\AutocompleteRequest;
use Illuminate\Support\Facades\Validator;

describe('AutocompleteRequest Unit Tests', function (): void {
    it('authorizes all requests', function (): void {
        $request = new AutocompleteRequest;

        expect($request->authorize())->toBeTrue();
    });

    it('validates required query parameter', function (): void {
        $request = new AutocompleteRequest;

        $validator = Validator::make([], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('q'))->toBeTrue();
    });

    it('validates query min length', function (): void {
        $request = new AutocompleteRequest;

        $validator = Validator::make(['q' => 'a'], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('q'))->toBeTrue();
    });

    it('validates query max length', function (): void {
        $request = new AutocompleteRequest;

        $validator = Validator::make(['q' => str_repeat('a', 256)], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('q'))->toBeTrue();
    });

    it('validates limit must be integer', function (): void {
        $request = new AutocompleteRequest;

        $validator = Validator::make(['q' => 'test', 'limit' => 'invalid'], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('limit'))->toBeTrue();
    });

    it('validates limit min value', function (): void {
        $request = new AutocompleteRequest;

        $validator = Validator::make(['q' => 'test', 'limit' => 0], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('limit'))->toBeTrue();
    });

    it('validates limit max value', function (): void {
        $request = new AutocompleteRequest;

        $validator = Validator::make(['q' => 'test', 'limit' => 21], $request->rules());

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('limit'))->toBeTrue();
    });

    it('accepts valid data', function (): void {
        $request = new AutocompleteRequest;

        $validator = Validator::make(['q' => 'test', 'limit' => 10], $request->rules());

        expect($validator->passes())->toBeTrue();
    });

    it('returns query string', function (): void {
        $request = AutocompleteRequest::create('/search/autocomplete', 'GET', ['q' => 'laravel']);

        expect($request->getQuery())->toBe('laravel');
    });

    it('returns empty string when query is not string', function (): void {
        $request = AutocompleteRequest::create('/search/autocomplete', 'GET', ['q' => ['array']]);

        expect($request->getQuery())->toBe('');
    });

    it('returns limit value', function (): void {
        $request = AutocompleteRequest::create('/search/autocomplete', 'GET', ['q' => 'test', 'limit' => 15]);

        expect($request->getLimit())->toBe(15);
    });

    it('returns default limit when not provided', function (): void {
        $request = AutocompleteRequest::create('/search/autocomplete', 'GET', ['q' => 'test']);

        expect($request->getLimit())->toBe(10);
    });

    it('returns default limit when value is not numeric', function (): void {
        $request = AutocompleteRequest::create('/search/autocomplete', 'GET', ['q' => 'test', 'limit' => 'invalid']);

        expect($request->getLimit())->toBe(10);
    });

    it('converts numeric string to integer for limit', function (): void {
        $request = AutocompleteRequest::create('/search/autocomplete', 'GET', ['q' => 'test', 'limit' => '5']);

        expect($request->getLimit())->toBe(5);
    });
});
