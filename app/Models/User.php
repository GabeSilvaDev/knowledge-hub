<?php

namespace App\Models;

use App\Exceptions\TokenCreationException;
use Database\Factories\UserFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\Sanctum;
use MongoDB\Laravel\Auth\User as Authenticatable;

/**
 * User Model.
 *
 * Represents a user account in the system with authentication, roles, and relationships
 * to articles and tokens. Uses MongoDB for storage and Laravel Sanctum for API authentication.
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'mongodb';

    /** @var string */
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'avatar_url',
        'bio',
        'roles',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get the articles written by this user.
     *
     * Defines the one-to-many relationship between users and their articles.
     *
     * @return HasMany<Article, User> The articles relationship
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    /**
     * Get the comments written by this user.
     *
     * @return HasMany<Comment, User>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'user_id');
    }

    /**
     * Get the likes made by this user.
     *
     * @return HasMany<Like, User>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'user_id');
    }

    /**
     * Get the users this user is following.
     *
     * @return HasMany<Follower, User>
     */
    public function following(): HasMany
    {
        return $this->hasMany(Follower::class, 'follower_id');
    }

    /**
     * Get the users following this user.
     *
     * @return HasMany<Follower, User>
     */
    public function followers(): HasMany
    {
        return $this->hasMany(Follower::class, 'following_id');
    }

    /**
     * Override tokens relationship to use MongoDB connection.
     *
     * Customizes Sanctum's token relationship to work with MongoDB's _id field
     * instead of the default id field.
     *
     * @return MorphMany<PersonalAccessToken, User> The tokens relationship
     */
    public function tokens(): MorphMany
    {
        return $this->morphMany(
            Sanctum::personalAccessTokenModel(),
            'tokenable',
            'tokenable_type',
            'tokenable_id',
            '_id'
        );
    }

    /**
     * Create a new personal access token for the user.
     *
     * Generates a new Sanctum API token with specified name and abilities.
     * Overrides the default implementation to ensure MongoDB compatibility.
     *
     * @param  string  $name  The token name
     * @param  array<int, string>  $abilities  The token abilities/permissions (default: ['*'])
     * @param  DateTimeInterface|null  $expiresAt  Optional expiration date
     * @return NewAccessToken The created access token
     *
     * @throws TokenCreationException If token creation fails
     */
    public function createToken(string $name, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null): NewAccessToken
    {
        $plainTextToken = $this->generateTokenString();

        $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        $token = $this->tokens()
            ->where('name', $name)
            ->where('token', hash('sha256', $plainTextToken))
            ->first();

        if ($token === null) {
            throw new TokenCreationException('Failed to create token: token not found after creation');
        }

        $tokenKey = $token->getKey();

        if ($tokenKey === null) {
            throw new TokenCreationException('Failed to create token: token key is null');
        }

        return new NewAccessToken($token, $tokenKey . '|' . $plainTextToken);
    }
}
