<?php

use App\DTOs\CreateUserDTO;
use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

const TEST_USER_NAME = 'Test User';
const TEST_EMAIL = 'test@example.com';
const JOHN_DOE_NAME = 'John Doe';
const JOHN_EMAIL = 'john@example.com';
const JANE_SMITH_NAME = 'Jane Smith';
const JANE_EMAIL = 'jane@example.com';

function setupUserRepository(): UserRepository
{
    return new UserRepository(new User);
}

function cleanupAfterUserTest(): void
{
    User::query()->delete();
}

function testUserFindMethods(): void
{
    describe('findById method', function (): void {
        it('returns user when found', function (): void {
            $repository = setupUserRepository();
            $user = User::factory()->create();

            $result = $repository->findById($user->_id);

            expect($result)->not->toBeNull()
                ->and($result->_id)->toBe($user->_id)
                ->and($result->name)->toBe($user->name)
                ->and($result->email)->toBe($user->email);
        });

        it('returns null when user not found', function (): void {
            $repository = setupUserRepository();
            $result = $repository->findById('507f1f77bcf86cd799439011');

            expect($result)->toBeNull();
        });
    });

    describe('findByEmail method', function (): void {
        it('returns user when found by email', function (): void {
            $repository = setupUserRepository();
            $user = User::factory()->create(['email' => TEST_EMAIL]);

            $result = $repository->findByEmail(TEST_EMAIL);

            expect($result)->not->toBeNull()
                ->and($result->email)->toBe(TEST_EMAIL)
                ->and($result->_id)->toBe($user->_id);
        });

        it('returns null when user not found by email', function (): void {
            $repository = setupUserRepository();
            $result = $repository->findByEmail('nonexistent@example.com');

            expect($result)->toBeNull();
        });
    });

    describe('findByUsername method', function (): void {
        it('returns user when found by username', function (): void {
            $repository = setupUserRepository();
            $user = User::factory()->create(['username' => 'testuser']);

            $result = $repository->findByUsername('testuser');

            expect($result)->not->toBeNull()
                ->and($result->username)->toBe('testuser')
                ->and($result->_id)->toBe($user->_id);
        });

        it('returns null when user not found by username', function (): void {
            $repository = setupUserRepository();
            $result = $repository->findByUsername('nonexistentuser');

            expect($result)->toBeNull();
        });
    });
}

function testUserCreateMethod(): void
{
    describe('create method', function (): void {
        it('creates user from DTO', function (): void {
            $repository = setupUserRepository();
            $dto = CreateUserDTO::fromArray([
                'name' => TEST_USER_NAME,
                'username' => 'testuser',
                'email' => TEST_EMAIL,
                'password' => 'Password123!',
                'roles' => [UserRole::READER],
            ]);

            $result = $repository->create($dto);

            expect($result)->toBeInstanceOf(User::class)
                ->and($result->name)->toBe(TEST_USER_NAME)
                ->and($result->username)->toBe('testuser')
                ->and($result->email)->toBe(TEST_EMAIL)
                ->and($result->roles)->toBe([UserRole::READER->value]);
        });

        it('creates user with minimal required fields', function (): void {
            $repository = setupUserRepository();
            $dto = CreateUserDTO::fromArray([
                'name' => 'Minimal User',
                'username' => 'minimaluser',
                'email' => 'minimal@example.com',
                'password' => 'Password123!',
            ]);

            $result = $repository->create($dto);

            expect($result)->toBeInstanceOf(User::class)
                ->and($result->name)->toBe('Minimal User')
                ->and($result->username)->toBe('minimaluser')
                ->and($result->email)->toBe('minimal@example.com')
                ->and($result->roles)->toBe([UserRole::READER->value]);
        });
    });
}

function testUserPaginationMethods(): void
{
    describe('paginate method', function (): void {
        it('paginates users with default per page', function (): void {
            $repository = setupUserRepository();
            User::factory()->count(20)->create();

            $result = $repository->paginate();

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->perPage())->toBe(15)
                ->and($result->total())->toBe(20)
                ->and(count($result->items()))->toBe(15);
        });

        it('paginates users with custom per page', function (): void {
            $repository = setupUserRepository();
            User::factory()->count(25)->create();

            $result = $repository->paginate(10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->perPage())->toBe(10)
                ->and($result->total())->toBe(25)
                ->and(count($result->items()))->toBe(10);
        });

        it('returns empty pagination when no users exist', function (): void {
            $repository = setupUserRepository();

            $result = $repository->paginate();

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->total())->toBe(0)
                ->and(count($result->items()))->toBe(0);
        });
    });
}

function testUserRoleMethods(): void
{
    describe('getByRole method', function (): void {
        it('returns users with specific role', function (): void {
            $repository = setupUserRepository();
            User::factory()->count(3)->admin()->create();
            User::factory()->count(5)->reader()->create();

            $result = $repository->getByRole(UserRole::ADMIN->value);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);

            foreach ($result as $user) {
                expect($user->roles)->toContain(UserRole::ADMIN->value);
            }
        });

        it('returns users with author role', function (): void {
            $repository = setupUserRepository();
            User::factory()->count(2)->author()->create();
            User::factory()->count(2)->reader()->create();

            $result = $repository->getByRole(UserRole::AUTHOR->value);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(2);

            foreach ($result as $user) {
                expect($user->roles)->toContain(UserRole::AUTHOR->value);
            }
        });

        it('returns empty collection when no users have the role', function (): void {
            $repository = setupUserRepository();
            User::factory()->count(5)->reader()->create();

            $result = $repository->getByRole(UserRole::ADMIN->value);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(0);
        });

        it('returns users with multiple roles including searched role', function (): void {
            $repository = setupUserRepository();
            User::factory()->create([
                'roles' => [UserRole::READER->value, UserRole::ADMIN->value, UserRole::AUTHOR->value],
            ]);

            $result = $repository->getByRole(UserRole::ADMIN->value);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1);
        });
    });
}

function testUserSearchMethods(): void
{
    describe('search method', function (): void {
        it('searches users by name', function (): void {
            $repository = setupUserRepository();
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);
            User::factory()->create(['name' => JANE_SMITH_NAME, 'username' => 'janesmith', 'email' => JANE_EMAIL]);

            $result = $repository->search('John');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1)
                ->and($result->first()->name)->toBe(JOHN_DOE_NAME);
        });

        it('searches users by username', function (): void {
            $repository = setupUserRepository();
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);
            User::factory()->create(['name' => JANE_SMITH_NAME, 'username' => 'janesmith', 'email' => JANE_EMAIL]);

            $result = $repository->search('johndoe');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1)
                ->and($result->first()->username)->toBe('johndoe');
        });

        it('searches users by email', function (): void {
            $repository = setupUserRepository();
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);
            User::factory()->create(['name' => JANE_SMITH_NAME, 'username' => 'janesmith', 'email' => JANE_EMAIL]);

            $result = $repository->search(JOHN_EMAIL);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1)
                ->and($result->first()->email)->toBe(JOHN_EMAIL);
        });

        it('searches users across multiple fields', function (): void {
            $repository = setupUserRepository();
            User::factory()->create(['name' => TEST_USER_NAME, 'username' => 'testuser', 'email' => TEST_EMAIL]);
            User::factory()->create(['name' => 'Another User', 'username' => 'anotheruser', 'email' => 'another@test.com']);
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);
            User::factory()->create(['name' => JANE_SMITH_NAME, 'username' => 'janesmith', 'email' => 'jane@testdomain.com']);

            $result = $repository->search('test');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);
        });

        it('returns empty collection when no users match search term', function (): void {
            $repository = setupUserRepository();
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);

            $result = $repository->search('nonexistent');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(0);
        });

        it('is case insensitive', function (): void {
            $repository = setupUserRepository();
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);

            $result = $repository->search('JOHN');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1)
                ->and($result->first()->name)->toBe(JOHN_DOE_NAME);
        });
    });
}

function testUserConstructor(): void
{
    describe('constructor', function (): void {
        it('creates repository with User model dependency', function (): void {
            $model = new User;
            $repository = new UserRepository($model);

            expect($repository)->toBeInstanceOf(UserRepository::class);
        });
    });
}

describe('UserRepository', function (): void {
    afterEach(function (): void {
        cleanupAfterUserTest();
    });

    testUserFindMethods();
    testUserCreateMethod();
    testUserPaginationMethods();
    testUserRoleMethods();
    testUserSearchMethods();
    testUserConstructor();
});
