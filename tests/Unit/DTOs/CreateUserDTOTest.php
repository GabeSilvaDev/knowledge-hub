<?php

use App\DTOs\CreateUserDTO;
use App\Enums\UserRole;
use App\ValueObjects\Bio;
use App\ValueObjects\Email;
use App\ValueObjects\Name;
use App\ValueObjects\Password;
use App\ValueObjects\Url;
use App\ValueObjects\UserCredentials;
use App\ValueObjects\Username;
use App\ValueObjects\UserProfile;

describe('CreateUserDTO Construction', function (): void {
    it('creates dto with required properties', function (): void {
        $profile = UserProfile::create(
            name: Name::from('John Doe'),
            username: Username::from('johndoe'),
            bio: Bio::from('Software developer')
        );

        $credentials = UserCredentials::create(
            email: Email::from('john@example.com'),
            password: Password::fromPlainText('SecurePass123!'),
            roles: [UserRole::READER]
        );

        $dto = new CreateUserDTO(
            profile: $profile,
            credentials: $credentials
        );

        expect($dto->profile)->toBe($profile)
            ->and($dto->credentials)->toBe($credentials)
            ->and($dto->avatar_url)->toBeNull();
    });

    it('creates dto with all properties including avatar', function (): void {
        $profile = UserProfile::create(
            name: Name::from('Jane Smith'),
            username: Username::from('janesmith'),
            bio: Bio::from('Product manager and tech enthusiast')
        );

        $credentials = UserCredentials::create(
            email: Email::from('jane@example.com'),
            password: Password::fromPlainText('AnotherPass456!'),
            roles: [UserRole::ADMIN, UserRole::AUTHOR]
        );

        $avatarUrl = Url::from('https://example.com/avatar.jpg');

        $dto = new CreateUserDTO(
            profile: $profile,
            credentials: $credentials,
            avatar_url: $avatarUrl
        );

        expect($dto->profile)->toBe($profile)
            ->and($dto->credentials)->toBe($credentials)
            ->and($dto->avatar_url)->toBe($avatarUrl);
    });

    it('creates dto with null bio', function (): void {
        $profile = UserProfile::create(
            name: Name::from('Bob Wilson'),
            username: Username::from('bobwilson'),
            bio: Bio::from(null)
        );

        $credentials = UserCredentials::create(
            email: Email::from('bob@example.com'),
            password: Password::fromPlainText('BobPass789!'),
            roles: [UserRole::AUTHOR]
        );

        $dto = new CreateUserDTO(
            profile: $profile,
            credentials: $credentials
        );

        expect($dto->profile->getBio()->getValue())->toBeNull();
    });
});

describe('CreateUserDTO toArray', function (): void {
    it('converts to array with minimal data', function (): void {
        $profile = UserProfile::create(
            name: Name::from('Alice Johnson'),
            username: Username::from('alicejohnson'),
            bio: Bio::from('Designer')
        );

        $credentials = UserCredentials::create(
            email: Email::from('alice@example.com'),
            password: Password::fromPlainText('AlicePass123!'),
            roles: [UserRole::READER]
        );

        $dto = new CreateUserDTO(
            profile: $profile,
            credentials: $credentials
        );

        $array = $dto->toArray();

        expect($array)->toHaveKey('name', 'Alice Johnson')
            ->and($array)->toHaveKey('email', 'alice@example.com')
            ->and($array)->toHaveKey('username', 'alicejohnson')
            ->and($array)->toHaveKey('bio', 'Designer')
            ->and($array)->toHaveKey('avatar_url', null)
            ->and($array)->toHaveKey('roles', ['reader'])
            ->and($array)->toHaveKey('password')
            ->and($array['password'])->toBeString();
    });

    it('converts to array with all data including avatar', function (): void {
        $profile = UserProfile::create(
            name: Name::from('Mike Brown'),
            username: Username::from('mikebrown'),
            bio: Bio::from('Full stack developer')
        );

        $credentials = UserCredentials::create(
            email: Email::from('mike@example.com'),
            password: Password::fromPlainText('MikePass456!'),
            roles: [UserRole::ADMIN, UserRole::AUTHOR, UserRole::READER]
        );

        $avatarUrl = Url::from('https://example.com/mike-avatar.png');

        $dto = new CreateUserDTO(
            profile: $profile,
            credentials: $credentials,
            avatar_url: $avatarUrl
        );

        $array = $dto->toArray();

        expect($array)->toHaveKey('name', 'Mike Brown')
            ->and($array)->toHaveKey('email', 'mike@example.com')
            ->and($array)->toHaveKey('username', 'mikebrown')
            ->and($array)->toHaveKey('bio', 'Full stack developer')
            ->and($array)->toHaveKey('avatar_url', 'https://example.com/mike-avatar.png')
            ->and($array)->toHaveKey('roles', ['admin', 'author', 'reader'])
            ->and($array['password'])->toBeString();
    });

    it('converts to array with null bio', function (): void {
        $profile = UserProfile::create(
            name: Name::from('Sarah Davis'),
            username: Username::from('sarahdavis'),
            bio: Bio::from(null)
        );

        $credentials = UserCredentials::create(
            email: Email::from('sarah@example.com'),
            password: Password::fromPlainText('SarahPass789!'),
            roles: [UserRole::AUTHOR]
        );

        $dto = new CreateUserDTO(
            profile: $profile,
            credentials: $credentials
        );

        $array = $dto->toArray();

        expect($array)->toHaveKey('bio', null)
            ->and($array)->toHaveKey('roles', ['author']);
    });
});

describe('CreateUserDTO fromArray', function (): void {
    it('creates from array with minimal data', function (): void {
        $data = [
            'name' => 'Tom Wilson',
            'username' => 'tomwilson',
            'email' => 'tom@example.com',
            'password' => 'TomPass123!',
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->profile->getName()->getValue())->toBe('Tom Wilson')
            ->and($dto->profile->getUsername()->getValue())->toBe('tomwilson')
            ->and($dto->profile->getBio()->getValue())->toBeNull()
            ->and($dto->credentials->getEmail()->getValue())->toBe('tom@example.com')
            ->and($dto->credentials->getRoles())->toBe([UserRole::READER])
            ->and($dto->avatar_url)->toBeNull();
    });

    it('creates from array with bio', function (): void {
        $data = [
            'name' => 'Lisa Garcia',
            'username' => 'lisagarcia',
            'email' => 'lisa@example.com',
            'password' => 'LisaPass456!',
            'bio' => 'UX/UI Designer',
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->profile->getBio()->getValue())->toBe('UX/UI Designer');
    });

    it('creates from array with custom roles', function (): void {
        $data = [
            'name' => 'David Lee',
            'username' => 'davidlee',
            'email' => 'david@example.com',
            'password' => 'DavidPass789!',
            'roles' => [UserRole::ADMIN, UserRole::AUTHOR],
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->credentials->getRoles())->toBe([UserRole::ADMIN, UserRole::AUTHOR]);
    });

    it('creates from array with avatar url', function (): void {
        $data = [
            'name' => 'Emma Taylor',
            'username' => 'emmataylor',
            'email' => 'emma@example.com',
            'password' => 'EmmaPass123!',
            'avatar_url' => 'https://example.com/emma.jpg',
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->avatar_url->getValue())->toBe('https://example.com/emma.jpg');
    });

    it('creates from array with all optional data', function (): void {
        $data = [
            'name' => 'Chris Anderson',
            'username' => 'chrisanderson',
            'email' => 'chris@example.com',
            'password' => 'ChrisPass456!',
            'bio' => 'DevOps Engineer',
            'avatar_url' => 'https://example.com/chris-profile.png',
            'roles' => [UserRole::ADMIN],
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->profile->getName()->getValue())->toBe('Chris Anderson')
            ->and($dto->profile->getUsername()->getValue())->toBe('chrisanderson')
            ->and($dto->profile->getBio()->getValue())->toBe('DevOps Engineer')
            ->and($dto->credentials->getEmail()->getValue())->toBe('chris@example.com')
            ->and($dto->credentials->getRoles())->toBe([UserRole::ADMIN])
            ->and($dto->avatar_url->getValue())->toBe('https://example.com/chris-profile.png');
    });

    it('creates from array with empty bio string', function (): void {
        $data = [
            'name' => 'Rachel Green',
            'username' => 'rachelgreen',
            'email' => 'rachel@example.com',
            'password' => 'RachelPass789!',
            'bio' => '',
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->profile->getBio()->getValue())->toBe('');
    });
});

describe('CreateUserDTO Validation Errors', function (): void {
    it('throws exception when name is not a string', function (): void {
        $data = [
            'name' => 123,
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
        ];

        CreateUserDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Name, username, email and password must be strings');

    it('throws exception when username is not a string', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => ['invalid'],
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
        ];

        CreateUserDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Name, username, email and password must be strings');

    it('throws exception when email is not a string', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 999,
            'password' => 'TestPass123!',
        ];

        CreateUserDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Name, username, email and password must be strings');

    it('throws exception when password is not a string', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => true,
        ];

        CreateUserDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Name, username, email and password must be strings');

    it('throws exception when bio is not a string or null', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
            'bio' => 123,
        ];

        CreateUserDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Bio must be a string or null');

    it('throws exception when avatar_url is not a string or null', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
            'avatar_url' => false,
        ];

        CreateUserDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Avatar URL must be a string or null');

    it('throws exception when roles is not an array', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
            'roles' => 'not-an-array',
        ];

        CreateUserDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Roles must be an array');

    it('throws exception when role item is invalid type', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
            'roles' => [123, 'valid-role'],
        ];

        CreateUserDTO::fromArray($data);
    })->throws(InvalidArgumentException::class, 'Each role must be a UserRole enum or valid string');
});

describe('CreateUserDTO Array Handling', function (): void {
    it('accepts roles as UserRole enums', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
            'roles' => [UserRole::ADMIN, UserRole::AUTHOR],
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->credentials->getRoles())->toBe([UserRole::ADMIN, UserRole::AUTHOR]);
    });

    it('accepts roles as strings', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
            'roles' => ['admin', 'author'],
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->credentials->getRoles())->toBe([UserRole::ADMIN, UserRole::AUTHOR]);
    });

    it('accepts mixed roles (UserRole enums and strings)', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
            'roles' => [UserRole::ADMIN, 'author', UserRole::READER],
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->credentials->getRoles())->toBe([UserRole::ADMIN, UserRole::AUTHOR, UserRole::READER]);
    });

    it('uses default READER role when roles array is empty', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
            'roles' => [],
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->credentials->getRoles())->toBe([UserRole::READER]);
    });

    it('handles null bio correctly', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
            'bio' => null,
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->profile->getBio()->getValue())->toBeNull();
    });

    it('handles null avatar_url correctly', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'TestPass123!',
            'avatar_url' => null,
        ];

        $dto = CreateUserDTO::fromArray($data);

        expect($dto->avatar_url)->toBeNull();
    });
});
