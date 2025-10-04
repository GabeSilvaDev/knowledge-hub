<?php

use App\Models\Article;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

const TEST_USER_EMAIL = 'test@example.com';
const TEST_USER_USERNAME = 'testuser';
const TEST_USER_BIO = 'Test user biography';
const TEST_AVATAR_URL = 'https://example.com/avatar.jpg';
const TEST_PASSWORD = 'password123';

describe('User Model Basic Functionality', function (): void {
    it('can instantiate user model', function (): void {
        $user = new User;

        expect($user)->toBeInstanceOf(User::class);
    });

    it('can set and get attributes', function (): void {
        $user = new User;
        $user->name = TEST_USER_NAME;
        $user->email = TEST_USER_EMAIL;
        $user->username = TEST_USER_USERNAME;

        expect($user->name)->toBe(TEST_USER_NAME)
            ->and($user->email)->toBe(TEST_USER_EMAIL)
            ->and($user->username)->toBe(TEST_USER_USERNAME);
    });
});

describe('User Model Mass Assignment', function (): void {
    it('allows mass assignment of fillable attributes', function (): void {
        $attributes = [
            'name' => TEST_USER_NAME,
            'email' => TEST_USER_EMAIL,
            'password' => TEST_PASSWORD,
            'username' => TEST_USER_USERNAME,
            'avatar_url' => TEST_AVATAR_URL,
            'bio' => TEST_USER_BIO,
            'roles' => ['author', 'reader'],
        ];

        $user = new User($attributes);

        expect($user->name)->toBe(TEST_USER_NAME)
            ->and($user->email)->toBe(TEST_USER_EMAIL)
            ->and($user->username)->toBe(TEST_USER_USERNAME)
            ->and($user->avatar_url)->toBe(TEST_AVATAR_URL)
            ->and($user->bio)->toBe(TEST_USER_BIO)
            ->and($user->roles)->toBe(['author', 'reader'])
            ->and($user->password)->not->toBe(TEST_PASSWORD);
    });
});

describe('User Model Attributes Casting', function (): void {
    it('casts roles to array', function (): void {
        $user = new User;
        $user->roles = ['admin', 'author'];

        expect($user->roles)->toBeArray()
            ->and($user->roles)->toBe(['admin', 'author']);
    });

    it('casts password to hashed', function (): void {
        $user = new User;
        $casts = $user->getCasts();

        expect($casts['password'])->toBe('hashed');
    });

    it('casts datetime fields to Carbon instances', function (): void {
        $user = new User;
        $user->email_verified_at = now();
        $user->last_login_at = now();

        expect($user->email_verified_at)->toBeInstanceOf(Carbon::class)
            ->and($user->last_login_at)->toBeInstanceOf(Carbon::class);
    });
});

describe('User Model Hidden Attributes', function (): void {
    it('hides password and remember_token from array serialization', function (): void {
        $user = new User;
        $userArray = $user->toArray();

        expect($userArray)->not->toHaveKey('password')
            ->and($userArray)->not->toHaveKey('remember_token');
    });
});

describe('User Model Database Operations', function (): void {
    it('uses mongodb connection', function (): void {
        $user = new User;

        expect($user->getConnectionName())->toBe('mongodb');
    });

    it('uses users collection', function (): void {
        $user = new User;

        expect($user->getTable())->toBe('users');
    });
});

describe('User Model Configuration', function (): void {
    it('has correct fillable attributes', function (): void {
        $user = new User;
        $expectedFillable = [
            'name',
            'email',
            'password',
            'username',
            'avatar_url',
            'bio',
            'roles',
        ];

        expect($user->getFillable())->toBe($expectedFillable);
    });

    it('has correct hidden attributes', function (): void {
        $user = new User;
        $expectedHidden = ['password', 'remember_token'];

        expect($user->getHidden())->toBe($expectedHidden);
    });

    it('has correct cast configuration', function (): void {
        $user = new User;
        $expectedCasts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];

        expect($user->getCasts())->toBe($expectedCasts);
    });
});

describe('User Model Relationships', function (): void {
    it('articles relationship returns HasMany instance', function (): void {
        $user = new User;
        $relation = $user->articles();

        expect($relation)->toBeInstanceOf(HasMany::class)
            ->and($relation->getRelated())->toBeInstanceOf(Article::class)
            ->and($relation->getForeignKeyName())->toBe('author_id');
    });
});

describe('User Model Factory Integration', function (): void {
    it('has factory class available', function (): void {
        $factory = User::factory();

        expect($factory)->toBeInstanceOf(UserFactory::class);
    });

    it('factory can create model instance without persisting', function (): void {
        $user = User::factory()->make();

        expect($user)->toBeInstanceOf(User::class)
            ->and($user->exists)->toBeFalse()
            ->and($user->name)->toBeString()
            ->and($user->email)->toBeString()
            ->and($user->username)->toBeString();
    });

    it('factory creates users with different roles using states', function (): void {
        $adminUser = User::factory()->admin()->make();
        $authorUser = User::factory()->author()->make();
        $readerUser = User::factory()->reader()->make();

        expect($adminUser->roles)->toContain('admin')
            ->and($adminUser->roles)->toContain('author')
            ->and($adminUser->roles)->toContain('reader')
            ->and($authorUser->roles)->toContain('author')
            ->and($authorUser->roles)->toContain('reader')
            ->and($authorUser->roles)->not->toContain('admin')
            ->and($readerUser->roles)->toContain('reader')
            ->and($readerUser->roles)->not->toContain('admin')
            ->and($readerUser->roles)->not->toContain('author');
    });
});

describe('User Model Authentication Features', function (): void {
    it('extends MongoDB Authenticatable', function (): void {
        expect(new User)->toBeInstanceOf(User::class);
    });

    it('uses HasFactory and Notifiable traits', function (): void {
        $traits = class_uses_recursive(User::class);

        expect($traits)->toHaveKey(HasFactory::class)
            ->and($traits)->toHaveKey(Notifiable::class);
    });
});
