<?php

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;

const VALID_EMAIL = 'test@example.com';

describe('LoginRequest Authorization', function (): void {
    it('authorize method returns true', function (): void {
        $request = new LoginRequest;

        expect($request->authorize())->toBeTrue();
    });
});

describe('LoginRequest Validation Rules', function (): void {
    it('rules method returns correct validation rules', function (): void {
        $request = new LoginRequest;
        $rules = $request->rules();

        expect($rules)->toBeArray()
            ->and($rules)->toHaveKey('email')
            ->and($rules)->toHaveKey('password')
            ->and($rules['email'])->toContain('required')
            ->and($rules['email'])->toContain('email')
            ->and($rules['password'])->toContain('required')
            ->and($rules['password'])->toContain('string');
    });

    it('email is required', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['password' => 'password123'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue();
    });

    it('email must be valid format', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => 'invalid-email', 'password' => 'password123'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue();
    });

    it('password is required', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => VALID_EMAIL],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });

    it('password must be a string', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => VALID_EMAIL, 'password' => 12345],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });

    it('validation passes with valid email and password', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => VALID_EMAIL, 'password' => 'password123'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeFalse();
    });

    it('validation accepts any valid email format', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => 'user.name+tag@example.co.uk', 'password' => 'password123'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeFalse();
    });

    it('validation fails with empty string email', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => '', 'password' => 'password123'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue();
    });

    it('validation fails with empty string password', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => VALID_EMAIL, 'password' => ''],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });

    it('validation fails with null email', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => null, 'password' => 'password123'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue();
    });

    it('validation fails with null password', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => VALID_EMAIL, 'password' => null],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });

    it('validation fails with both fields missing', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            [],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });
});

describe('LoginRequest Custom Messages', function (): void {
    it('messages method returns correct custom messages', function (): void {
        $request = new LoginRequest;
        $messages = $request->messages();

        expect($messages)->toBeArray()
            ->and($messages)->toHaveKey('email.required')
            ->and($messages)->toHaveKey('email.email')
            ->and($messages)->toHaveKey('password.required')
            ->and($messages['email.required'])->toBe('O email é obrigatório.')
            ->and($messages['email.email'])->toBe('O email deve ser um endereço válido.')
            ->and($messages['password.required'])->toBe('A senha é obrigatória.');
    });

    it('custom message is used for email required error', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['password' => 'password123'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->errors()->first('email'))->toBe('O email é obrigatório.');
    });

    it('custom message is used for email format error', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => 'invalid-email', 'password' => 'password123'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->errors()->first('email'))->toBe('O email deve ser um endereço válido.');
    });

    it('custom message is used for password required error', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => VALID_EMAIL],
            $request->rules(),
            $request->messages()
        );

        expect($validator->errors()->first('password'))->toBe('A senha é obrigatória.');
    });
});

describe('LoginRequest Custom Attributes', function (): void {
    it('attributes method returns correct custom attributes', function (): void {
        $request = new LoginRequest;
        $attributes = $request->attributes();

        expect($attributes)->toBeArray()
            ->and($attributes)->toHaveKey('email')
            ->and($attributes)->toHaveKey('password')
            ->and($attributes['email'])->toBe('email')
            ->and($attributes['password'])->toBe('senha');
    });
});

describe('LoginRequest Edge Cases', function (): void {
    it('validation accepts very long but valid password', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => VALID_EMAIL, 'password' => str_repeat('a', 1000)],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeFalse();
    });

    it('validation accepts email with subdomain', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => 'test@mail.example.com', 'password' => 'password123'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeFalse();
    });

    it('validation rejects email without domain', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => 'test@', 'password' => 'password123'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue();
    });

    it('validation rejects email without at symbol', function (): void {
        $request = new LoginRequest;
        $validator = Validator::make(
            ['email' => 'testexample.com', 'password' => 'password123'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue();
    });
});
