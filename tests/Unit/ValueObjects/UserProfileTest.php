<?php

use App\ValueObjects\Bio;
use App\ValueObjects\Name;
use App\ValueObjects\Username;
use App\ValueObjects\UserProfile;

const TEST_BIO_TEXT = 'Test Bio';
const JOHNDOE_USERNAME = 'johndoe';
const TESTUSER_USERNAME = 'testuser';
const DEVELOPER_BIO = 'Developer';

describe('UserProfile ValueObject Tests', function (): void {
    describe('construction and creation', function (): void {
        it('creates user profile with constructor', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(JOHNDOE_USERNAME);
            $bio = Bio::from('Software Developer');

            $profile = new UserProfile($name, $username, $bio);

            expect($profile->getName())->toBe($name)
                ->and($profile->getUsername())->toBe($username)
                ->and($profile->getBio())->toBe($bio);
        });

        it('creates user profile using static factory method', function (): void {
            $name = Name::from('Jane Smith');
            $username = Username::from('janesmith');
            $bio = Bio::from('Product Manager');

            $profile = UserProfile::create($name, $username, $bio);

            expect($profile)->toBeInstanceOf(UserProfile::class)
                ->and($profile->getName())->toBe($name)
                ->and($profile->getUsername())->toBe($username)
                ->and($profile->getBio())->toBe($bio);
        });

        it('creates user profile without bio', function (): void {
            $name = Name::from('Bob Wilson');
            $username = Username::from('bobwilson');

            $profile = new UserProfile($name, $username);

            expect($profile->getName())->toBe($name)
                ->and($profile->getUsername())->toBe($username)
                ->and($profile->getBio())->toBeNull();
        });

        it('creates user profile using factory without bio', function (): void {
            $name = Name::from('Alice Johnson');
            $username = Username::from('alicejohnson');

            $profile = UserProfile::create($name, $username);

            expect($profile->getName())->toBe($name)
                ->and($profile->getUsername())->toBe($username)
                ->and($profile->getBio())->toBeNull();
        });

        it('creates user profile with null bio explicitly', function (): void {
            $name = Name::from('Charlie Brown');
            $username = Username::from('charliebrown');

            $profile = UserProfile::create($name, $username, null);

            expect($profile->getName())->toBe($name)
                ->and($profile->getUsername())->toBe($username)
                ->and($profile->getBio())->toBeNull();
        });
    });

    describe('getter methods', function (): void {
        it('returns name correctly', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(TESTUSER_USERNAME);
            $profile = new UserProfile($name, $username);

            expect($profile->getName())->toBe($name)
                ->and($profile->getName()->getValue())->toBe(JOHN_DOE_NAME);
        });

        it('returns username correctly', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(TESTUSER_USERNAME);
            $profile = new UserProfile($name, $username);

            expect($profile->getUsername())->toBe($username)
                ->and($profile->getUsername()->getValue())->toBe(TESTUSER_USERNAME);
        });

        it('returns bio correctly when set', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(TESTUSER_USERNAME);
            $bio = Bio::from(TEST_BIO_TEXT);
            $profile = new UserProfile($name, $username, $bio);

            expect($profile->getBio())->toBe($bio)
                ->and($profile->getBio()->getValue())->toBe(TEST_BIO_TEXT);
        });

        it('returns null bio when not set', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(TESTUSER_USERNAME);
            $profile = new UserProfile($name, $username);

            expect($profile->getBio())->toBeNull();
        });
    });
});

describe('UserProfile Equality Tests', function (): void {
    describe('equality comparison', function (): void {
        it('returns true for identical profiles with bio', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(JOHNDOE_USERNAME);
            $bio = Bio::from(DEVELOPER_BIO);

            $profile1 = new UserProfile($name, $username, $bio);
            $profile2 = new UserProfile($name, $username, $bio);

            expect($profile1->equals($profile2))->toBeTrue();
        });

        it('returns true for identical profiles without bio', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(JOHNDOE_USERNAME);

            $profile1 = new UserProfile($name, $username);
            $profile2 = new UserProfile($name, $username);

            expect($profile1->equals($profile2))->toBeTrue();
        });

        it('returns false for different names', function (): void {
            $name1 = Name::from(JOHN_DOE_NAME);
            $name2 = Name::from('Jane Doe');
            $username = Username::from(JOHNDOE_USERNAME);

            $profile1 = new UserProfile($name1, $username);
            $profile2 = new UserProfile($name2, $username);

            expect($profile1->equals($profile2))->toBeFalse();
        });

        it('returns false for different usernames', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username1 = Username::from(JOHNDOE_USERNAME);
            $username2 = Username::from('johndoe2');

            $profile1 = new UserProfile($name, $username1);
            $profile2 = new UserProfile($name, $username2);

            expect($profile1->equals($profile2))->toBeFalse();
        });

        it('returns false for different bios', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(JOHNDOE_USERNAME);
            $bio1 = Bio::from(DEVELOPER_BIO);
            $bio2 = Bio::from('Designer');

            $profile1 = new UserProfile($name, $username, $bio1);
            $profile2 = new UserProfile($name, $username, $bio2);

            expect($profile1->equals($profile2))->toBeFalse();
        });

        it('returns false when one has bio and other does not', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(JOHNDOE_USERNAME);
            $bio = Bio::from(DEVELOPER_BIO);

            $profile1 = new UserProfile($name, $username, $bio);
            $profile2 = new UserProfile($name, $username);

            expect($profile1->equals($profile2))->toBeFalse();
        });

        it('returns false when other has bio and first does not', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(JOHNDOE_USERNAME);
            $bio = Bio::from(DEVELOPER_BIO);

            $profile1 = new UserProfile($name, $username);
            $profile2 = new UserProfile($name, $username, $bio);

            expect($profile1->equals($profile2))->toBeFalse();
        });

        it('returns true for profiles with equivalent value objects', function (): void {
            $profile1 = new UserProfile(
                Name::from(JOHN_DOE_NAME),
                Username::from(JOHNDOE_USERNAME),
                Bio::from(DEVELOPER_BIO)
            );

            $profile2 = new UserProfile(
                Name::from(JOHN_DOE_NAME),
                Username::from(JOHNDOE_USERNAME),
                Bio::from(DEVELOPER_BIO)
            );

            expect($profile1->equals($profile2))->toBeTrue();
        });
    });
});

describe('UserProfile Properties Tests', function (): void {
    describe('readonly properties', function (): void {
        it('exposes name as public readonly property', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(TESTUSER_USERNAME);
            $profile = new UserProfile($name, $username);

            expect($profile->name)->toBe($name);
        });

        it('exposes username as public readonly property', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(TESTUSER_USERNAME);
            $profile = new UserProfile($name, $username);

            expect($profile->username)->toBe($username);
        });

        it('exposes bio as public readonly property', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(TESTUSER_USERNAME);
            $bio = Bio::from(TEST_BIO_TEXT);
            $profile = new UserProfile($name, $username, $bio);

            expect($profile->bio)->toBe($bio);
        });

        it('exposes null bio as public readonly property', function (): void {
            $name = Name::from(JOHN_DOE_NAME);
            $username = Username::from(TESTUSER_USERNAME);
            $profile = new UserProfile($name, $username);

            expect($profile->bio)->toBeNull();
        });
    });
});
