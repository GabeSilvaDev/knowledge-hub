<?php

use App\DTOs\CreateUserDTO;
use App\Enums\UserRole;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

const TEST_EMAIL = 'test@example.com';
const JOHN_DOE_NAME = 'John Doe';
const JOHN_EMAIL = 'john@example.com';
const JANE_SMITH_NAME = 'Jane Smith';
const JANE_EMAIL = 'jane@example.com';

describe('UserRepository', function (): void {
    beforeEach(function (): void {
        $this->repository = new UserRepository(new User);
    });

    afterEach(function (): void {
        User::query()->delete();
    });

    describe('constructor', function (): void {
        it('creates repository with User model dependency', function (): void {
            expect($this->repository)->toBeInstanceOf(UserRepository::class);
        });
    });

    describe('findById method', function (): void {
        it('returns user when found', function (): void {
            $user = User::factory()->create();

            $result = $this->repository->findById($user->_id);

            expect($result)->not->toBeNull()
                ->and($result->_id)->toBe($user->_id)
                ->and($result->name)->toBe($user->name)
                ->and($result->email)->toBe($user->email);
        });

        it('returns null when user not found', function (): void {
            $result = $this->repository->findById('507f1f77bcf86cd799439011');

            expect($result)->toBeNull();
        });
    });

    describe('findByEmail method', function (): void {
        it('returns user when found by email', function (): void {
            $user = User::factory()->create(['email' => TEST_EMAIL]);

            $result = $this->repository->findByEmail(TEST_EMAIL);

            expect($result)->not->toBeNull()
                ->and($result->email)->toBe(TEST_EMAIL)
                ->and($result->_id)->toBe($user->_id);
        });

        it('returns null when user not found by email', function (): void {
            $result = $this->repository->findByEmail('nonexistent@example.com');

            expect($result)->toBeNull();
        });
    });

    describe('findByUsername method', function (): void {
        it('returns user when found by username', function (): void {
            $user = User::factory()->create(['username' => 'testuser']);

            $result = $this->repository->findByUsername('testuser');

            expect($result)->not->toBeNull()
                ->and($result->username)->toBe('testuser')
                ->and($result->_id)->toBe($user->_id);
        });

        it('returns null when user not found by username', function (): void {
            $result = $this->repository->findByUsername('nonexistentuser');

            expect($result)->toBeNull();
        });
    });

    describe('create method', function (): void {
        it('creates user from DTO', function (): void {
            $dto = CreateUserDTO::fromArray([
                'name' => 'Test User',
                'username' => 'testuser',
                'email' => TEST_EMAIL,
                'password' => 'Password123!',
                'roles' => [UserRole::READER],
            ]);

            $result = $this->repository->create($dto);

            expect($result)->toBeInstanceOf(User::class)
                ->and($result->name)->toBe('Test User')
                ->and($result->username)->toBe('testuser')
                ->and($result->email)->toBe(TEST_EMAIL)
                ->and($result->roles)->toBe([UserRole::READER->value]);
        });

        it('creates user with minimal required fields', function (): void {
            $dto = CreateUserDTO::fromArray([
                'name' => 'Minimal User',
                'username' => 'minimaluser',
                'email' => 'minimal@example.com',
                'password' => 'Password123!',
            ]);

            $result = $this->repository->create($dto);

            expect($result)->toBeInstanceOf(User::class)
                ->and($result->name)->toBe('Minimal User')
                ->and($result->username)->toBe('minimaluser')
                ->and($result->email)->toBe('minimal@example.com')
                ->and($result->roles)->toBe([UserRole::READER->value]);
        });
    });

    describe('paginate method', function (): void {
        it('paginates users with default per page', function (): void {
            User::factory()->count(20)->create();

            $result = $this->repository->paginate();

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->perPage())->toBe(15)
                ->and($result->total())->toBe(20)
                ->and(count($result->items()))->toBe(15);
        });

        it('paginates users with custom per page', function (): void {
            User::factory()->count(25)->create();

            $result = $this->repository->paginate(10);

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->perPage())->toBe(10)
                ->and($result->total())->toBe(25)
                ->and(count($result->items()))->toBe(10);
        });

        it('returns empty pagination when no users exist', function (): void {
            $result = $this->repository->paginate();

            expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
                ->and($result->total())->toBe(0)
                ->and(count($result->items()))->toBe(0);
        });
    });

    describe('getByRole method', function (): void {
        it('returns users with specific role', function (): void {
            User::factory()->count(3)->admin()->create();
            User::factory()->count(5)->reader()->create();

            $result = $this->repository->getByRole(UserRole::ADMIN->value);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);

            foreach ($result as $user) {
                expect($user->roles)->toContain(UserRole::ADMIN->value);
            }
        });

        it('returns users with author role', function (): void {
            User::factory()->count(2)->author()->create();
            User::factory()->count(2)->reader()->create();

            $result = $this->repository->getByRole(UserRole::AUTHOR->value);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(2);

            foreach ($result as $user) {
                expect($user->roles)->toContain(UserRole::AUTHOR->value);
            }
        });

        it('returns empty collection when no users have the role', function (): void {
            User::factory()->count(5)->reader()->create();

            $result = $this->repository->getByRole(UserRole::ADMIN->value);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(0);
        });

        it('returns users with multiple roles including searched role', function (): void {
            User::factory()->create([
                'roles' => [UserRole::READER->value, UserRole::ADMIN->value, UserRole::AUTHOR->value],
            ]);

            $result = $this->repository->getByRole(UserRole::ADMIN->value);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1);
        });
    });

    describe('search method', function (): void {
        it('searches users by name', function (): void {
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);
            User::factory()->create(['name' => JANE_SMITH_NAME, 'username' => 'janesmith', 'email' => JANE_EMAIL]);

            $result = $this->repository->search('John');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1)
                ->and($result->first()->name)->toBe(JOHN_DOE_NAME);
        });

        it('searches users by username', function (): void {
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);
            User::factory()->create(['name' => JANE_SMITH_NAME, 'username' => 'janesmith', 'email' => JANE_EMAIL]);

            $result = $this->repository->search('johndoe');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1)
                ->and($result->first()->username)->toBe('johndoe');
        });

        it('searches users by email', function (): void {
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);
            User::factory()->create(['name' => JANE_SMITH_NAME, 'username' => 'janesmith', 'email' => JANE_EMAIL]);

            $result = $this->repository->search(JOHN_EMAIL);

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1)
                ->and($result->first()->email)->toBe(JOHN_EMAIL);
        });

        it('searches users across multiple fields', function (): void {
            User::factory()->create(['name' => 'Test User', 'username' => 'testuser', 'email' => TEST_EMAIL]);
            User::factory()->create(['name' => 'Another User', 'username' => 'anotheruser', 'email' => 'another@test.com']);
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);
            User::factory()->create(['name' => JANE_SMITH_NAME, 'username' => 'janesmith', 'email' => 'jane@testdomain.com']);

            $result = $this->repository->search('test');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(3);
        });

        it('returns empty collection when no users match search term', function (): void {
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);

            $result = $this->repository->search('nonexistent');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(0);
        });

        it('is case insensitive', function (): void {
            User::factory()->create(['name' => JOHN_DOE_NAME, 'username' => 'johndoe', 'email' => JOHN_EMAIL]);

            $result = $this->repository->search('JOHN');

            expect($result)->toBeInstanceOf(Collection::class)
                ->and($result->count())->toBe(1)
                ->and($result->first()->name)->toBe(JOHN_DOE_NAME);
        });
    });
});
