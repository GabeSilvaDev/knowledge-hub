<?php

use App\Exceptions\TokenCreationException;
use App\Models\Article;
use App\Models\PersonalAccessToken;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use MongoDB\Laravel\Auth\User as Authenticatable;

use function Pest\Laravel\mock;

describe('User Model Basic Functionality', function (): void {
    it('can instantiate user model', function (): void {
        $user = new User;

        expect($user)->toBeInstanceOf(User::class)
            ->and($user)->toBeInstanceOf(Authenticatable::class);
    });

    it('can set and get attributes', function (): void {
        $user = new User;
        $user->name = 'John Doe';
        $user->email = 'john@example.com';

        expect($user->name)->toBe('John Doe')
            ->and($user->email)->toBe('john@example.com');
    });

    it('uses HasApiTokens trait', function (): void {
        $traits = class_uses(User::class);

        expect($traits)->toHaveKey(HasApiTokens::class);
    });
});

describe('User Model Configuration', function (): void {
    it('uses mongodb connection', function (): void {
        $user = new User;

        expect($user->getConnectionName())->toBe('mongodb');
    });

    it('uses users collection', function (): void {
        $user = new User;

        expect($user->getTable())->toBe('users');
    });

    it('has correct fillable attributes', function (): void {
        $user = new User;
        $expected = [
            'name',
            'email',
            'password',
            'username',
            'avatar_url',
            'bio',
            'roles',
            'last_login_at',
        ];

        expect($user->getFillable())->toBe($expected);
    });

    it('has correct hidden attributes', function (): void {
        $user = new User;
        $expected = ['password', 'remember_token'];

        expect($user->getHidden())->toBe($expected);
    });

    it('has correct cast configuration', function (): void {
        $user = new User;
        $casts = $user->getCasts();

        expect($casts)->toHaveKeys([
            'email_verified_at',
            'password',
            'created_at',
            'updated_at',
            'last_login_at',
        ]);
    });
});

describe('User Model Attributes Casting', function (): void {
    beforeEach(function (): void {
        User::query()->forceDelete();
    });

    it('casts roles to array', function (): void {
        $user = User::factory()->create([
            'roles' => ['admin', 'editor'],
        ]);

        expect($user->roles)->toBeArray()
            ->and($user->roles)->toBe(['admin', 'editor']);
    });

    it('hashes password attribute', function (): void {
        $user = User::factory()->create([
            'password' => 'plain-password',
        ]);

        expect($user->password)->not->toBe('plain-password')
            ->and(Hash::check('plain-password', $user->password))->toBeTrue();
    });

    it('casts datetime fields to Carbon instances', function (): void {
        $user = User::factory()->create();

        expect($user->created_at)->toBeInstanceOf(Carbon::class)
            ->and($user->updated_at)->toBeInstanceOf(Carbon::class);
    });
});

describe('User Model Hidden Attributes', function (): void {
    beforeEach(function (): void {
        User::query()->forceDelete();
    });

    it('hides password from array serialization', function (): void {
        $user = User::factory()->create();
        $array = $user->toArray();

        expect($array)->not->toHaveKey('password');
    });

    it('hides remember_token from array serialization', function (): void {
        $user = User::factory()->create();
        $array = $user->toArray();

        expect($array)->not->toHaveKey('remember_token');
    });
});

describe('User Model Relationships', function (): void {
    beforeEach(function (): void {
        User::query()->forceDelete();
        Article::query()->forceDelete();
    });

    it('articles relationship returns HasMany instance', function (): void {
        $user = new User;
        $relation = $user->articles();

        expect($relation)->toBeInstanceOf(HasMany::class);
    });

    it('can retrieve user articles', function (): void {
        $user = User::factory()->create();
        $article = Article::factory()->create(['author_id' => $user->_id]);

        $articles = $user->articles;

        expect($articles)->toHaveCount(1)
            ->and($articles->first()->_id)->toBe($article->_id);
    });

    it('tokens relationship returns MorphMany instance', function (): void {
        $user = new User;
        $relation = $user->tokens();

        expect($relation)->toBeInstanceOf(MorphMany::class);
    });
});

describe('User Model Factory Integration', function (): void {
    it('has factory class available', function (): void {
        expect(User::factory())->toBeInstanceOf(UserFactory::class);
    });

    it('factory can create model instance without persisting', function (): void {
        $user = User::factory()->make();

        expect($user)->toBeInstanceOf(User::class)
            ->and($user->exists)->toBeFalse();
    });
});

describe('User Model Token Creation', function (): void {
    beforeEach(function (): void {
        User::query()->forceDelete();
        PersonalAccessToken::query()->forceDelete();
    });

    it('can create token successfully', function (): void {
        $user = User::factory()->create();

        $result = $user->createToken('API Token');

        expect($result)->toBeInstanceOf(NewAccessToken::class)
            ->and($result->plainTextToken)->toContain('|')
            ->and($result->accessToken)->toBeInstanceOf(PersonalAccessToken::class);
    });

    it('can create token with specific abilities', function (): void {
        $user = User::factory()->create();

        $result = $user->createToken('My Token', ['read', 'write']);

        expect($result->accessToken->abilities)->toBe(['read', 'write']);
    });

    it('can create token with expiration date', function (): void {
        $user = User::factory()->create();
        $expiresAt = now()->addDays(7);

        $result = $user->createToken('Expiring Token', ['*'], $expiresAt);

        expect($result->accessToken->expires_at)->not->toBeNull();
    });

    it('creates token with valid string key', function (): void {
        $user = User::factory()->create();

        $result = $user->createToken('Valid Token');

        $key = $result->accessToken->getKey();
        expect($key)->toBeString()
            ->and(strlen($key))->toBe(24);
    });

    it('token key is properly formatted in plain text token', function (): void {
        $user = User::factory()->create();

        $result = $user->createToken('Format Test');

        $parts = explode('|', $result->plainTextToken);

        expect($parts)->toHaveCount(2)
            ->and($parts[0])->toBeString()
            ->and(strlen($parts[0]))->toBe(24)
            ->and($parts[1])->toBeString();
    });

    it('validates token key is string or int type', function (): void {
        $user = User::factory()->create();

        $plainTextToken = bin2hex(random_bytes(40));

        $user->tokens()->create([
            'name' => 'Test Token',
            'token' => hash('sha256', $plainTextToken),
            'abilities' => ['*'],
        ]);

        $token = $user->tokens()
            ->where('name', 'Test Token')
            ->where('token', hash('sha256', $plainTextToken))
            ->first();

        $tokenKey = $token->getKey();
        expect($tokenKey)->toBeString();

        $isValidType = is_string($tokenKey) || is_int($tokenKey);
        expect($isValidType)->toBeTrue();
    });

    it('creates token and validates key format in access token', function (): void {
        $user = User::factory()->create();

        $result = $user->createToken('Validation Test');

        $tokenKey = $result->accessToken->getKey();

        expect($tokenKey)->toBeString();

        expect($result->plainTextToken)->toContain((string) $tokenKey);
    });

    it('throws exception when token is not found after creation', function (): void {
        /** @var MorphMany&Mockery\MockInterface $mockRelation */
        $mockRelation = mock(MorphMany::class);
        $mockRelation->shouldReceive('create')->andReturn(true);
        $mockRelation->shouldReceive('where')->andReturnSelf();
        $mockRelation->shouldReceive('first')->andReturn(null);

        /** @var User&Mockery\MockInterface $user */
        $user = mock(User::class)->makePartial();
        $user->shouldReceive('tokens')->andReturn($mockRelation);
        $user->shouldReceive('generateTokenString')->andReturn(bin2hex(random_bytes(40)));

        expect(fn () => $user->createToken('API Token'))
            ->toThrow(TokenCreationException::class, 'Failed to create token: token not found after creation');
    });

    it('throws exception when token key is null', function (): void {
        /** @var PersonalAccessToken&Mockery\MockInterface $mockToken */
        $mockToken = mock(PersonalAccessToken::class);
        $mockToken->shouldReceive('getKey')->andReturn(null);

        /** @var MorphMany&Mockery\MockInterface $mockRelation */
        $mockRelation = mock(MorphMany::class);
        $mockRelation->shouldReceive('create')->andReturn(true);
        $mockRelation->shouldReceive('where')->andReturnSelf();
        $mockRelation->shouldReceive('first')->andReturn($mockToken);

        /** @var User&Mockery\MockInterface $user */
        $user = mock(User::class)->makePartial();
        $user->shouldReceive('tokens')->andReturn($mockRelation);
        $user->shouldReceive('generateTokenString')->andReturn(bin2hex(random_bytes(40)));

        expect(fn () => $user->createToken('API Token'))
            ->toThrow(TokenCreationException::class, 'Failed to create token: token key is null');
    });
});

describe('User Model Database Operations', function (): void {
    beforeEach(function (): void {
        User::query()->forceDelete();
    });

    it('can create user', function (): void {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        expect($user)->toBeInstanceOf(User::class)
            ->and($user->name)->toBe('Test User')
            ->and($user->email)->toBe('test@example.com');
    });

    it('can update user', function (): void {
        $user = User::factory()->create(['name' => 'Old Name']);

        $user->update(['name' => 'New Name']);

        expect($user->fresh()->name)->toBe('New Name');
    });

    it('can delete user', function (): void {
        $user = User::factory()->create();
        $userId = $user->_id;

        $user->delete();

        expect(User::find($userId))->toBeNull();
    });
});
