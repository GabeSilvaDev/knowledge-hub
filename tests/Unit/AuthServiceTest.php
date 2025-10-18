<?php

use App\Contracts\UserRepositoryInterface;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    User::query()->delete();
    DB::connection('mongodb')->getCollection('personal_access_tokens')->deleteMany([]);
});

afterEach(function () {
    User::query()->delete();
    DB::connection('mongodb')->getCollection('personal_access_tokens')->deleteMany([]);
});

describe('register', function () {
    it('creates user with all required fields', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'username' => 'johndoe',
            'password' => 'Password123',
        ];

        $result = $service->register($data);

        expect($result)->toHaveKeys(['user', 'token'])
            ->and($result['user'])->toBeInstanceOf(User::class)
            ->and($result['user']->name)->toBe('John Doe')
            ->and($result['user']->email)->toBe('john@example.com')
            ->and($result['user']->username)->toBe('johndoe')
            ->and($result['user']->roles)->toBe(['reader'])
            ->and($result['token'])->toBeString()
            ->and(Hash::check('Password123', $result['user']->password))->toBeTrue();
    });

    it('creates user with optional bio and avatar_url', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $data = [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'username' => 'janesmith',
            'password' => 'Password123',
            'bio' => 'Software developer',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ];

        $result = $service->register($data);

        expect($result['user']->bio)->toBe('Software developer')
            ->and($result['user']->avatar_url)->toBe('https://example.com/avatar.jpg');
    });

    it('creates user without optional fields when not provided', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $data = [
            'name' => 'Bob Wilson',
            'email' => 'bob@example.com',
            'username' => 'bobwilson',
            'password' => 'Password123',
        ];

        $result = $service->register($data);

        expect($result['user']->bio)->toBeNull()
            ->and($result['user']->avatar_url)->toBeNull();
    });

    it('assigns default user role', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'username' => 'testuser',
            'password' => 'Password123',
        ];

        $result = $service->register($data);

        expect($result['user']->roles)->toBe(['reader']);
    });

    it('generates authentication token', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $data = [
            'name' => 'Token User',
            'email' => 'token@example.com',
            'username' => 'tokenuser',
            'password' => 'Password123',
        ];

        $result = $service->register($data);

        expect($result['token'])->toBeString()
            ->and(strlen($result['token']))->toBeGreaterThan(20)
            ->and($result['user']->tokens()->count())->toBe(1);
    });

    it('hashes password before storing', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $data = [
            'name' => 'Hash User',
            'email' => 'hash@example.com',
            'username' => 'hashuser',
            'password' => 'PlainPassword123',
        ];

        $result = $service->register($data);

        expect($result['user']->password)->not->toBe('PlainPassword123')
            ->and(Hash::check('PlainPassword123', $result['user']->password))->toBeTrue();
    });

    it('creates user in database', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $data = [
            'name' => 'DB User',
            'email' => 'db@example.com',
            'username' => 'dbuser',
            'password' => 'Password123',
        ];

        $service->register($data);

        $userInDb = User::where('email', 'db@example.com')->first();
        expect($userInDb)->not->toBeNull()
            ->and($userInDb->username)->toBe('dbuser');
    });
});

describe('login', function () {
    $incorrectCredentialsMessage = 'The provided credentials are incorrect.';

    it('authenticates user with valid credentials', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);
        $loginEmail = 'login@example.com';

        User::create([
            'name' => 'Login User',
            'email' => $loginEmail,
            'username' => 'loginuser',
            'password' => bcrypt('Password123'),
            'roles' => ['user'],
        ]);

        $result = $service->login($loginEmail, 'Password123');

        expect($result)->toHaveKeys(['user', 'token'])
            ->and($result['user'])->toBeInstanceOf(User::class)
            ->and($result['user']->email)->toBe($loginEmail)
            ->and($result['token'])->toBeString();
    });

    it('generates new token on login', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        User::create([
            'name' => 'Token Login',
            'email' => 'tokenlogin@example.com',
            'username' => 'tokenlogin',
            'password' => bcrypt('Password123'),
            'roles' => ['user'],
        ]);

        $result = $service->login('tokenlogin@example.com', 'Password123');

        expect($result['token'])->toBeString()
            ->and(strlen($result['token']))->toBeGreaterThan(20);
    });

    it('updates last_login_at timestamp', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $user = User::create([
            'name' => 'Timestamp User',
            'email' => 'timestamp@example.com',
            'username' => 'timestampuser',
            'password' => bcrypt('Password123'),
            'roles' => ['user'],
        ]);

        expect($user->last_login_at)->toBeNull();

        $service->login('timestamp@example.com', 'Password123');

        $user->refresh();
        expect($user->last_login_at)->not->toBeNull();
    });

    it('throws exception when email does not exist', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $service->login('nonexistent@example.com', 'Password123');
    })->throws(ValidationException::class, $incorrectCredentialsMessage);

    it('throws exception when password is incorrect', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        User::create([
            'name' => 'Wrong Pass',
            'email' => 'wrongpass@example.com',
            'username' => 'wrongpass',
            'password' => bcrypt('correctpassword'),
            'roles' => ['user'],
        ]);

        $service->login('wrongpass@example.com', 'wrongpassword');
    })->throws(ValidationException::class, $incorrectCredentialsMessage);

    it('throws exception with proper message format', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $this->expectException(ValidationException::class);
        $service->login('invalid@example.com', 'password');
    })
        ->throws(ValidationException::class);

    it('returns refreshed user instance', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        User::create([
            'name' => 'Refresh User',
            'email' => 'refresh@example.com',
            'username' => 'refreshuser',
            'password' => bcrypt('Password123'),
            'roles' => ['user'],
        ]);

        $result = $service->login('refresh@example.com', 'Password123');

        expect($result['user']->last_login_at)->not->toBeNull();
    });

    it('accepts case-sensitive password', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);
        $caseEmail = 'case@example.com';

        User::create([
            'name' => 'Case User',
            'email' => $caseEmail,
            'username' => 'caseuser',
            'password' => bcrypt('PassWord123'),
            'roles' => ['user'],
        ]);

        expect(fn() => $service->login($caseEmail, 'Password123'))
            ->toThrow(ValidationException::class);

        $result = $service->login($caseEmail, 'PassWord123');
        expect($result['user'])->toBeInstanceOf(User::class);
    });
});

describe('logout', function () {

    it('does nothing when token does not exist', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $user = User::create([
            'name' => 'No Token',
            'email' => 'notoken@example.com',
            'username' => 'notoken',
            'password' => bcrypt('Password123'),
            'roles' => ['user'],
        ]);

        $service->logout($user, 'nonexistent-token-id');

        expect($user->tokens()->count())->toBe(0);
    });
});

describe('revokeAllTokens', function () {
    it('revokes all user tokens', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $user = User::create([
            'name' => 'Revoke All',
            'email' => 'revokeall@example.com',
            'username' => 'revokeall',
            'password' => bcrypt('Password123'),
            'roles' => ['user'],
        ]);

        $user->createToken('token1');
        $user->createToken('token2');
        $user->createToken('token3');

        expect($user->tokens()->count())->toBe(3);

        $service->revokeAllTokens($user);

        expect($user->tokens()->count())->toBe(0);
    });

    it('does nothing when user has no tokens', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $user = User::create([
            'name' => 'No Tokens',
            'email' => 'notokens@example.com',
            'username' => 'notokens',
            'password' => bcrypt('Password123'),
            'roles' => ['user'],
        ]);

        expect($user->tokens()->count())->toBe(0);

        $service->revokeAllTokens($user);

        expect($user->tokens()->count())->toBe(0);
    });

    it('only revokes tokens for specified user', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);

        $user1 = User::create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'username' => 'userone',
            'password' => bcrypt('Password123'),
            'roles' => ['user'],
        ]);

        $user2 = User::create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'username' => 'usertwo',
            'password' => bcrypt('Password123'),
            'roles' => ['user'],
        ]);

        $user1->createToken('token1');
        $user1->createToken('token2');
        $user2->createToken('token3');
        $user2->createToken('token4');

        expect($user1->tokens()->count())->toBe(2);
        expect($user2->tokens()->count())->toBe(2);

        $service->revokeAllTokens($user1);

        expect($user1->tokens()->count())->toBe(0)
            ->and($user2->tokens()->count())->toBe(2);
    });
});

describe('Integration', function () {
    it('complete registration and login flow', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);
        $flowEmail = 'flow@example.com';

        $registerData = [
            'name' => 'Flow User',
            'email' => $flowEmail,
            'username' => 'flowuser',
            'password' => 'Password123',
        ];

        $registerResult = $service->register($registerData);

        expect($registerResult['user']->email)->toBe($flowEmail)
            ->and($registerResult['token'])->toBeString();

        $token = $registerResult['user']->tokens()->first();
        $service->logout($registerResult['user'], (string) $token->_id);
        expect($registerResult['user']->tokens()->count())->toBe(0);

        $loginResult = $service->login($flowEmail, 'Password123');
        expect($loginResult['token'])->toBeString()
            ->and($loginResult['user']->tokens()->count())->toBe(1);
    });

    it('multiple sessions management', function () {
        $repository = app(UserRepositoryInterface::class);
        $service = new AuthService($repository);
        $multiSessionEmail = 'multisession@example.com';

        $data = [
            'name' => 'Multi Session',
            'email' => $multiSessionEmail,
            'username' => 'multisession',
            'password' => 'Password123',
        ];

        $service->register($data);

        $login1 = $service->login($multiSessionEmail, 'Password123');
        $service->login($multiSessionEmail, 'Password123');
        $service->login($multiSessionEmail, 'Password123');

        expect($login1['user']->tokens()->count())->toBe(4);

        $service->revokeAllTokens($login1['user']);
        expect($login1['user']->tokens()->count())->toBe(0);
    });
});
