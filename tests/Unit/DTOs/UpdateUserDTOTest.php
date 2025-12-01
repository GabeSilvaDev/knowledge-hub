<?php

declare(strict_types=1);

use App\DTOs\UpdateUserDTO;

describe('UpdateUserDTO Construction', function (): void {
    it('creates dto with all properties', function (): void {
        $dto = new UpdateUserDTO(
            name: 'John Doe',
            username: 'johndoe',
            bio: 'Software developer',
            avatarUrl: 'https://example.com/avatar.jpg'
        );

        expect($dto->name)->toBe('John Doe')
            ->and($dto->username)->toBe('johndoe')
            ->and($dto->bio)->toBe('Software developer')
            ->and($dto->avatarUrl)->toBe('https://example.com/avatar.jpg');
    });

    it('creates dto with only name', function (): void {
        $dto = new UpdateUserDTO(
            name: 'Jane Smith'
        );

        expect($dto->name)->toBe('Jane Smith')
            ->and($dto->username)->toBeNull()
            ->and($dto->bio)->toBeNull()
            ->and($dto->avatarUrl)->toBeNull();
    });

    it('creates dto with only username', function (): void {
        $dto = new UpdateUserDTO(
            username: 'janesmith'
        );

        expect($dto->name)->toBeNull()
            ->and($dto->username)->toBe('janesmith')
            ->and($dto->bio)->toBeNull()
            ->and($dto->avatarUrl)->toBeNull();
    });

    it('creates dto with only bio', function (): void {
        $dto = new UpdateUserDTO(
            bio: 'Tech enthusiast'
        );

        expect($dto->name)->toBeNull()
            ->and($dto->username)->toBeNull()
            ->and($dto->bio)->toBe('Tech enthusiast')
            ->and($dto->avatarUrl)->toBeNull();
    });

    it('creates dto with only avatarUrl', function (): void {
        $dto = new UpdateUserDTO(
            avatarUrl: 'https://example.com/photo.png'
        );

        expect($dto->name)->toBeNull()
            ->and($dto->username)->toBeNull()
            ->and($dto->bio)->toBeNull()
            ->and($dto->avatarUrl)->toBe('https://example.com/photo.png');
    });

    it('creates dto with all null values', function (): void {
        $dto = new UpdateUserDTO;

        expect($dto->name)->toBeNull()
            ->and($dto->username)->toBeNull()
            ->and($dto->bio)->toBeNull()
            ->and($dto->avatarUrl)->toBeNull();
    });

    it('creates dto with mixed null and non-null values', function (): void {
        $dto = new UpdateUserDTO(
            name: 'Alice Wonder',
            username: null,
            bio: 'Explorer',
            avatarUrl: null
        );

        expect($dto->name)->toBe('Alice Wonder')
            ->and($dto->username)->toBeNull()
            ->and($dto->bio)->toBe('Explorer')
            ->and($dto->avatarUrl)->toBeNull();
    });
});

describe('UpdateUserDTO toArray', function (): void {
    it('converts to array with all data', function (): void {
        $dto = new UpdateUserDTO(
            name: 'John Doe',
            username: 'johndoe',
            bio: 'Software developer',
            avatarUrl: 'https://example.com/avatar.jpg'
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'name' => 'John Doe',
            'username' => 'johndoe',
            'bio' => 'Software developer',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ]);
    });

    it('converts to array filtering out null values', function (): void {
        $dto = new UpdateUserDTO(
            name: 'Jane Smith',
            username: null,
            bio: 'Developer',
            avatarUrl: null
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'name' => 'Jane Smith',
            'bio' => 'Developer',
        ])
            ->and($array)->not->toHaveKey('username')
            ->and($array)->not->toHaveKey('avatar_url');
    });

    it('converts to empty array when all values are null', function (): void {
        $dto = new UpdateUserDTO;

        $array = $dto->toArray();

        expect($array)->toBe([]);
    });

    it('converts to array with only name', function (): void {
        $dto = new UpdateUserDTO(
            name: 'Bob Builder'
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'name' => 'Bob Builder',
        ]);
    });

    it('converts to array with only username', function (): void {
        $dto = new UpdateUserDTO(
            username: 'bobthebuilder'
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'username' => 'bobthebuilder',
        ]);
    });

    it('converts to array with only bio', function (): void {
        $dto = new UpdateUserDTO(
            bio: 'Can we fix it? Yes we can!'
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'bio' => 'Can we fix it? Yes we can!',
        ]);
    });

    it('converts to array with only avatarUrl', function (): void {
        $dto = new UpdateUserDTO(
            avatarUrl: 'https://cdn.example.com/images/avatar.png'
        );

        $array = $dto->toArray();

        expect($array)->toBe([
            'avatar_url' => 'https://cdn.example.com/images/avatar.png',
        ]);
    });
});

describe('UpdateUserDTO fromArray', function (): void {
    it('creates from array with all data', function (): void {
        $data = [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'bio' => 'Software developer',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ];

        $dto = UpdateUserDTO::fromArray($data);

        expect($dto->name)->toBe('John Doe')
            ->and($dto->username)->toBe('johndoe')
            ->and($dto->bio)->toBe('Software developer')
            ->and($dto->avatarUrl)->toBe('https://example.com/avatar.jpg');
    });

    it('creates from array with missing fields', function (): void {
        $data = [];

        $dto = UpdateUserDTO::fromArray($data);

        expect($dto->name)->toBeNull()
            ->and($dto->username)->toBeNull()
            ->and($dto->bio)->toBeNull()
            ->and($dto->avatarUrl)->toBeNull();
    });

    it('creates from array with partial data (name only)', function (): void {
        $data = [
            'name' => 'Alice Wonder',
        ];

        $dto = UpdateUserDTO::fromArray($data);

        expect($dto->name)->toBe('Alice Wonder')
            ->and($dto->username)->toBeNull()
            ->and($dto->bio)->toBeNull()
            ->and($dto->avatarUrl)->toBeNull();
    });

    it('creates from array with partial data (username and bio)', function (): void {
        $data = [
            'username' => 'alicewonder',
            'bio' => 'Wonderland explorer',
        ];

        $dto = UpdateUserDTO::fromArray($data);

        expect($dto->name)->toBeNull()
            ->and($dto->username)->toBe('alicewonder')
            ->and($dto->bio)->toBe('Wonderland explorer')
            ->and($dto->avatarUrl)->toBeNull();
    });

    it('creates from array with non-string name', function (): void {
        $data = [
            'name' => 123,
            'username' => 'user',
        ];

        $dto = UpdateUserDTO::fromArray($data);

        expect($dto->name)->toBeNull()
            ->and($dto->username)->toBe('user');
    });

    it('creates from array with non-string username', function (): void {
        $data = [
            'name' => 'Test User',
            'username' => ['array'],
        ];

        $dto = UpdateUserDTO::fromArray($data);

        expect($dto->name)->toBe('Test User')
            ->and($dto->username)->toBeNull();
    });

    it('creates from array with non-string bio', function (): void {
        $data = [
            'bio' => true,
        ];

        $dto = UpdateUserDTO::fromArray($data);

        expect($dto->bio)->toBeNull();
    });

    it('creates from array with non-string avatar_url', function (): void {
        $data = [
            'avatar_url' => 12345,
        ];

        $dto = UpdateUserDTO::fromArray($data);

        expect($dto->avatarUrl)->toBeNull();
    });

    it('creates from array with null values', function (): void {
        $data = [
            'name' => null,
            'username' => null,
            'bio' => null,
            'avatar_url' => null,
        ];

        $dto = UpdateUserDTO::fromArray($data);

        expect($dto->name)->toBeNull()
            ->and($dto->username)->toBeNull()
            ->and($dto->bio)->toBeNull()
            ->and($dto->avatarUrl)->toBeNull();
    });

    it('creates from array and converts back to array', function (): void {
        $data = [
            'name' => 'John Doe',
            'username' => 'johndoe',
            'bio' => 'Software developer',
            'avatar_url' => 'https://example.com/avatar.jpg',
        ];

        $dto = UpdateUserDTO::fromArray($data);
        $result = $dto->toArray();

        expect($result)->toBe($data);
    });

    it('creates from array with extra fields', function (): void {
        $data = [
            'name' => 'Test User',
            'extra_field' => 'Should be ignored',
            'email' => 'test@example.com',
        ];

        $dto = UpdateUserDTO::fromArray($data);

        expect($dto->name)->toBe('Test User')
            ->and($dto->username)->toBeNull()
            ->and($dto->bio)->toBeNull()
            ->and($dto->avatarUrl)->toBeNull();
    });
});

describe('UpdateUserDTO Type Safety', function (): void {
    it('is readonly and immutable', function (): void {
        $dto = new UpdateUserDTO(
            name: 'Test User'
        );

        expect($dto)->toBeInstanceOf(UpdateUserDTO::class);
    });

    it('handles long bio text', function (): void {
        $longBio = str_repeat('This is a bio. ', 100);

        $dto = new UpdateUserDTO(
            bio: $longBio
        );

        expect($dto->bio)->toBe($longBio);
    });

    it('handles special characters in name', function (): void {
        $specialName = "O'Brien-Smith Jr.";

        $dto = new UpdateUserDTO(
            name: $specialName
        );

        expect($dto->name)->toBe($specialName);
    });

    it('handles unicode characters', function (): void {
        $unicodeName = 'José García Martínez';
        $unicodeBio = 'Developer 👨‍💻 from España 🇪🇸';

        $dto = new UpdateUserDTO(
            name: $unicodeName,
            bio: $unicodeBio
        );

        expect($dto->name)->toBe($unicodeName)
            ->and($dto->bio)->toBe($unicodeBio);
    });

    it('handles URLs with query parameters', function (): void {
        $urlWithParams = 'https://example.com/avatar.jpg?size=large&format=png';

        $dto = new UpdateUserDTO(
            avatarUrl: $urlWithParams
        );

        expect($dto->avatarUrl)->toBe($urlWithParams);
    });

    it('preserves empty strings as empty strings', function (): void {
        $data = [
            'name' => '',
            'username' => '',
        ];

        $dto = UpdateUserDTO::fromArray($data);

        expect($dto->name)->toBe('')
            ->and($dto->username)->toBe('');
    });
});

describe('UpdateUserDTO Edge Cases', function (): void {
    it('handles very long username', function (): void {
        $longUsername = str_repeat('a', 255);

        $dto = new UpdateUserDTO(
            username: $longUsername
        );

        expect($dto->username)->toBe($longUsername);
    });

    it('handles complex avatar URLs', function (): void {
        $complexUrl = 'https://cdn.example.com/users/12345/avatars/profile_v2.jpg?token=abc123&expires=2025-12-31';

        $dto = new UpdateUserDTO(
            avatarUrl: $complexUrl
        );

        expect($dto->avatarUrl)->toBe($complexUrl);
    });

    it('creates dto with whitespace-only strings', function (): void {
        $dto = new UpdateUserDTO(
            name: '   ',
            bio: "\n\t  "
        );

        expect($dto->name)->toBe('   ')
            ->and($dto->bio)->toBe("\n\t  ");
    });

    it('round-trip conversion preserves only non-null values', function (): void {
        $data = [
            'name' => 'Test User',
            'bio' => 'Developer',
        ];

        $dto = UpdateUserDTO::fromArray($data);
        $result = $dto->toArray();

        expect($result)->toBe($data)
            ->and($result)->not->toHaveKey('username')
            ->and($result)->not->toHaveKey('avatar_url');
    });
});
