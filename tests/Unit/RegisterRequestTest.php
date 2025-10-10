<?php

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

const TEST_USER_NAME = 'Test User';
const TEST_USER_EMAIL = 'test@example.com';
const TEST_USER_USERNAME = 'testuser';
const TEST_USER_PASSWORD = 'password123';

const EXISTING_USER_NAME = 'Existing User';
const EXISTING_USER_EMAIL = 'existing@example.com';
const EXISTING_USER_USERNAME = 'existinguser';

beforeEach(function () {
    User::query()->delete();
});

afterEach(function () {
    User::query()->delete();
});

describe('RegisterRequest Authorization', function () {
    it('authorize method returns true', function () {
        $request = new RegisterRequest();
        
        expect($request->authorize())->toBeTrue();
    });
});

describe('RegisterRequest General Validation', function () {
    it('rules method returns correct validation rules', function () {
        $request = new RegisterRequest();
        $rules = $request->rules();

        expect($rules)->toBeArray()
            ->and($rules)->toHaveKey('name')
            ->and($rules)->toHaveKey('email')
            ->and($rules)->toHaveKey('username')
            ->and($rules)->toHaveKey('password')
            ->and($rules)->toHaveKey('bio')
            ->and($rules)->toHaveKey('avatar_url');
    });

    it('validation passes with all valid data', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            [
                'name' => TEST_USER_NAME,
                'email' => TEST_USER_EMAIL,
                'username' => TEST_USER_USERNAME,
                'password' => TEST_USER_PASSWORD,
                'password_confirmation' => TEST_USER_PASSWORD,
                'bio' => 'A short bio',
                'avatar_url' => 'https://example.com/avatar.jpg',
            ],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeFalse();
    });
});

describe('RegisterRequest Name Validation', function () {
    it('name is required', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('name'))->toBeTrue();
    });

    it('name must be a string', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => 123, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue();
    });

    it('name must have minimum 3 characters', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => 'ab', 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('name'))->toBeTrue();
    });

    it('name must not exceed 255 characters', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => str_repeat('a', 256), 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('name'))->toBeTrue();
    });
});

describe('RegisterRequest Email Validation', function () {
    it('email is required', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue();
    });

    it('email must be valid format', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => 'invalid-email', 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue();
    });

    it('email must be unique', function () {
        User::create([
            'name' => EXISTING_USER_NAME,
            'email' => EXISTING_USER_EMAIL,
            'username' => EXISTING_USER_USERNAME,
            'password' => bcrypt(TEST_USER_PASSWORD),
            'roles' => ['user'],
        ]);

        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => EXISTING_USER_EMAIL, 'username' => 'newuser', 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('email'))->toBeTrue();
    });
});

describe('RegisterRequest Username Validation', function () {
    it('username is required', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('username'))->toBeTrue();
    });

    it('username must have minimum 3 characters', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => 'ab', 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('username'))->toBeTrue();
    });

    it('username must not exceed 255 characters', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => str_repeat('a', 256), 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('username'))->toBeTrue();
    });

    it('username must be unique', function () {
        User::create([
            'name' => EXISTING_USER_NAME,
            'email' => EXISTING_USER_EMAIL,
            'username' => EXISTING_USER_USERNAME,
            'password' => bcrypt(TEST_USER_PASSWORD),
            'roles' => ['user'],
        ]);

        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => 'new@example.com', 'username' => EXISTING_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('username'))->toBeTrue();
    });

    it('username must be alpha dash', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => 'user name', 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('username'))->toBeTrue();
    });
});

describe('RegisterRequest Password Validation', function () {
    it('password is required', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });

    it('password must be confirmed', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => 'different'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });

    it('password must have minimum 8 characters', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => 'pass12', 'password_confirmation' => 'pass12'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });

    it('password must contain letters', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => '12345678', 'password_confirmation' => '12345678'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });

    it('password must contain numbers', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => 'abcdefgh', 'password_confirmation' => 'abcdefgh'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('password'))->toBeTrue();
    });
});

describe('RegisterRequest Optional Fields Validation', function () {
    it('bio is optional', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeFalse();
    });

    it('bio must not exceed 500 characters', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD, 'bio' => str_repeat('a', 501)],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('bio'))->toBeTrue();
    });

    it('avatar_url is optional', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeFalse();
    });

    it('avatar_url must be a valid URL', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD, 'avatar_url' => 'not-a-url'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('avatar_url'))->toBeTrue();
    });

    it('avatar_url must not exceed 500 characters', function () {
        $request = new RegisterRequest();
        $longUrl = 'https://example.com/' . str_repeat('a', 500);
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD, 'avatar_url' => $longUrl],
            $request->rules(),
            $request->messages()
        );

        expect($validator->fails())->toBeTrue()
            ->and($validator->errors()->has('avatar_url'))->toBeTrue();
    });
});

describe('RegisterRequest Custom Messages', function () {
    it('messages method returns correct custom messages', function () {
        $request = new RegisterRequest();
        $messages = $request->messages();

        expect($messages)->toBeArray()
            ->and($messages)->toHaveKey('name.required')
            ->and($messages)->toHaveKey('email.required')
            ->and($messages)->toHaveKey('username.required')
            ->and($messages)->toHaveKey('password.required')
            ->and($messages['name.required'])->toBe('O nome é obrigatório.')
            ->and($messages['email.required'])->toBe('O email é obrigatório.')
            ->and($messages['username.required'])->toBe('O nome de usuário é obrigatório.')
            ->and($messages['password.required'])->toBe('A senha é obrigatória.');
    });

    it('custom message is used for name required error', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->errors()->first('name'))->toBe('O nome é obrigatório.');
    });

    it('custom message is used for email unique error', function () {
        User::create([
            'name' => EXISTING_USER_NAME,
            'email' => EXISTING_USER_EMAIL,
            'username' => EXISTING_USER_USERNAME,
            'password' => bcrypt(TEST_USER_PASSWORD),
            'roles' => ['user'],
        ]);

        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => EXISTING_USER_EMAIL, 'username' => 'newuser', 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->errors()->first('email'))->toBe('Este email já está em uso.');
    });

    it('custom message is used for username alpha_dash error', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => 'user name', 'password' => TEST_USER_PASSWORD, 'password_confirmation' => TEST_USER_PASSWORD],
            $request->rules(),
            $request->messages()
        );

        expect($validator->errors()->first('username'))->toBe('O nome de usuário pode conter apenas letras, números, hífens e underscores.');
    });

    it('custom message is used for password confirmed error', function () {
        $request = new RegisterRequest();
        $validator = Validator::make(
            ['name' => TEST_USER_NAME, 'email' => TEST_USER_EMAIL, 'username' => TEST_USER_USERNAME, 'password' => TEST_USER_PASSWORD, 'password_confirmation' => 'different'],
            $request->rules(),
            $request->messages()
        );

        expect($validator->errors()->first('password'))->toBe('A confirmação de senha não confere.');
    });
});

describe('RegisterRequest Custom Attributes', function () {
    it('attributes method returns correct custom attributes', function () {
        $request = new RegisterRequest();
        $attributes = $request->attributes();

        expect($attributes)->toBeArray()
            ->and($attributes)->toHaveKey('name')
            ->and($attributes)->toHaveKey('email')
            ->and($attributes)->toHaveKey('username')
            ->and($attributes)->toHaveKey('password')
            ->and($attributes)->toHaveKey('bio')
            ->and($attributes)->toHaveKey('avatar_url')
            ->and($attributes['name'])->toBe('nome')
            ->and($attributes['email'])->toBe('email')
            ->and($attributes['username'])->toBe('nome de usuário')
            ->and($attributes['password'])->toBe('senha')
            ->and($attributes['bio'])->toBe('biografia')
            ->and($attributes['avatar_url'])->toBe('URL do avatar');
    });
});
