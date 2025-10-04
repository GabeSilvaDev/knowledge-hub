<?php

use App\Contracts\UserRepositoryInterface;
use App\DTOs\CreateUserDTO;
use App\Enums\UserRole;
use App\Models\User;
use App\Services\UserService;
use App\ValueObjects\Bio;
use App\ValueObjects\Email;
use App\ValueObjects\Name;
use App\ValueObjects\Password;
use App\ValueObjects\Url;
use App\ValueObjects\UserCredentials;
use App\ValueObjects\Username;
use App\ValueObjects\UserProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Mockery\MockInterface;

use function Pest\Laravel\mock;

describe('UserService', function (): void {
    it('creates a user', function (): void {
        /** @var UserRepositoryInterface|MockInterface $userRepository */
        $userRepository = mock(UserRepositoryInterface::class);
        $userService = new UserService($userRepository);

        $dto = new CreateUserDTO(
            profile: UserProfile::create(
                name: Name::from('John Doe'),
                username: Username::from('johndoe'),
                bio: Bio::from('Test bio')
            ),
            credentials: UserCredentials::create(
                email: Email::from('john@example.com'),
                password: Password::fromPlainText('SecurePass123!'),
                roles: [UserRole::READER]
            ),
            avatar_url: Url::from('https://example.com/avatar.jpg')
        );

        $user = new User;
        $userRepository
            ->shouldReceive('create')
            ->with($dto)
            ->once()
            ->andReturn($user);

        $result = $userService->createUser($dto);

        expect($result)->toBe($user);
    });

    it('gets user by id', function (): void {
        /** @var UserRepositoryInterface|MockInterface $userRepository */
        $userRepository = mock(UserRepositoryInterface::class);
        $userService = new UserService($userRepository);

        $userId = '507f1f77bcf86cd799439011';
        $user = new User;

        $userRepository
            ->shouldReceive('findById')
            ->with($userId)
            ->once()
            ->andReturn($user);

        $result = $userService->getUserById($userId);

        expect($result)->toBe($user);
    });

    it('gets user by email', function (): void {
        /** @var UserRepositoryInterface|MockInterface $userRepository */
        $userRepository = mock(UserRepositoryInterface::class);
        $userService = new UserService($userRepository);

        $email = 'test@example.com';
        $user = new User;

        $userRepository
            ->shouldReceive('findByEmail')
            ->with($email)
            ->once()
            ->andReturn($user);

        $result = $userService->getUserByEmail($email);

        expect($result)->toBe($user);
    });

    it('gets user by username', function (): void {
        /** @var UserRepositoryInterface|MockInterface $userRepository */
        $userRepository = mock(UserRepositoryInterface::class);
        $userService = new UserService($userRepository);

        $username = 'testuser';
        $user = new User;

        $userRepository
            ->shouldReceive('findByUsername')
            ->with($username)
            ->once()
            ->andReturn($user);

        $result = $userService->getUserByUsername($username);

        expect($result)->toBe($user);
    });

    it('gets paginated users', function (): void {
        /** @var UserRepositoryInterface|MockInterface $userRepository */
        $userRepository = mock(UserRepositoryInterface::class);
        $userService = new UserService($userRepository);

        $perPage = 10;
        $paginator = mock(LengthAwarePaginator::class);

        $userRepository
            ->shouldReceive('paginate')
            ->with($perPage)
            ->once()
            ->andReturn($paginator);

        $result = $userService->getUsers($perPage);

        expect($result)->toBe($paginator);
    });

    it('gets users by role', function (): void {
        /** @var UserRepositoryInterface|MockInterface $userRepository */
        $userRepository = mock(UserRepositoryInterface::class);
        $userService = new UserService($userRepository);

        $role = UserRole::AUTHOR;
        $users = new Collection;

        $userRepository
            ->shouldReceive('getByRole')
            ->with($role->value)
            ->once()
            ->andReturn($users);

        $result = $userService->getUsersByRole($role);

        expect($result)->toBe($users);
    });

    it('searches users', function (): void {
        /** @var UserRepositoryInterface|MockInterface $userRepository */
        $userRepository = mock(UserRepositoryInterface::class);
        $userService = new UserService($userRepository);

        $term = 'john';
        $users = new Collection;

        $userRepository
            ->shouldReceive('search')
            ->with($term)
            ->once()
            ->andReturn($users);

        $result = $userService->searchUsers($term);

        expect($result)->toBe($users);
    });
});
