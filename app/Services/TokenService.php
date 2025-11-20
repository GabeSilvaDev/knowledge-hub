<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Redis\Connections\Connection;
use Illuminate\Support\Facades\Redis;
use Laravel\Sanctum\NewAccessToken;

class TokenService
{
    private const string TOKEN_PREFIX = 'token:';

    private const string USER_TOKENS_PREFIX = 'user_tokens:';

    private const string REVOKED_TOKEN_PREFIX = 'revoked_token:';

    /**
     * Get the Redis connection for tokens.
     *
     * Returns the dedicated Redis connection configured for token storage.
     *
     * @return Connection The Redis connection instance
     */
    private function getConnection(): Connection
    {
        return Redis::connection('tokens');
    }

    /**
     * Store token metadata in Redis.
     *
     * Stores token information in Redis with TTL and adds to user's token set.
     *
     * @param  string  $tokenId  The token ID (hash of the token)
     * @param  int|string  $userId  The user ID
     * @param  int  $ttl  Time to live in seconds (default: 7 days)
     */
    public function storeTokenMetadata(string $tokenId, int|string $userId, int $ttl = 604800): void
    {
        $redis = $this->getConnection();

        $tokenKey = self::TOKEN_PREFIX . $tokenId;
        $userTokensKey = self::USER_TOKENS_PREFIX . $userId;

        $metadata = [
            'user_id' => (string) $userId,
            'created_at' => now()->toIso8601String(),
            'expires_at' => now()->addSeconds($ttl)->toIso8601String(),
            'last_used_at' => now()->toIso8601String(),
        ];

        $redis->hmset($tokenKey, $metadata);
        $redis->expire($tokenKey, $ttl);

        $redis->sadd($userTokensKey, $tokenId);
        $redis->expire($userTokensKey, $ttl + 86400);
    }

    /**
     * Update the last used timestamp for a token.
     *
     * Updates the last_used_at field in Redis to track token activity.
     *
     * @param  string  $tokenId  The token ID to update
     */
    public function updateTokenLastUsed(string $tokenId): void
    {
        $redis = $this->getConnection();
        $tokenKey = self::TOKEN_PREFIX . $tokenId;

        if ($redis->exists($tokenKey)) {
            $redis->hset($tokenKey, 'last_used_at', now()->toIso8601String());
        }
    }

    /**
     * Check if a token is revoked.
     *
     * Verifies if the token exists in the revoked tokens set in Redis.
     *
     * @param  string  $tokenId  The token ID to check
     * @return bool True if the token is revoked
     */
    public function isTokenRevoked(string $tokenId): bool
    {
        $redis = $this->getConnection();
        $revokedKey = self::REVOKED_TOKEN_PREFIX . $tokenId;

        return (bool) $redis->exists($revokedKey);
    }

    /**
     * Revoke a specific token.
     *
     * Marks the token as revoked in Redis and removes it from user's active tokens set.
     *
     * @param  string  $tokenId  The token ID (hash of the token)
     * @param  int  $ttl  How long to keep the revocation record (default: 30 days)
     */
    public function revokeToken(string $tokenId, int $ttl = 2592000): void
    {
        $redis = $this->getConnection();

        $tokenKey = self::TOKEN_PREFIX . $tokenId;
        $revokedKey = self::REVOKED_TOKEN_PREFIX . $tokenId;

        $userId = $redis->hget($tokenKey, 'user_id');

        $redis->setex($revokedKey, $ttl, json_encode([
            'revoked_at' => now()->toIso8601String(),
            'user_id' => $userId,
        ]));

        if ($userId) {
            $userTokensKey = self::USER_TOKENS_PREFIX . $userId;
            $redis->srem($userTokensKey, $tokenId);
        }

        $redis->del($tokenKey);
    }

    /**
     * Revoke all tokens for a user.
     *
     * Revokes all active tokens belonging to the specified user.
     *
     * @param  int|string  $userId  The user ID
     */
    public function revokeAllUserTokens(int|string $userId): void
    {
        $redis = $this->getConnection();
        $userTokensKey = self::USER_TOKENS_PREFIX . $userId;

        $tokenIds = $redis->smembers($userTokensKey);

        foreach ($tokenIds as $tokenId) {
            $this->revokeToken($tokenId);
        }

        $redis->del($userTokensKey);
    }

    /**
     * Get token metadata.
     *
     * Retrieves all metadata fields for a token from Redis.
     *
     * @param  string  $tokenId  The token ID to retrieve metadata for
     * @return array<string, mixed>|null Token metadata or null if not found
     */
    public function getTokenMetadata(string $tokenId): ?array
    {
        $redis = $this->getConnection();
        $tokenKey = self::TOKEN_PREFIX . $tokenId;

        if (! $redis->exists($tokenKey)) {
            return null;
        }

        $metadata = $redis->hgetall($tokenKey);

        if (! is_array($metadata) || $metadata === []) {
            return null;
        }

        // @phpstan-ignore-next-line
        return $metadata;
    }

    /**
     * Get all active tokens for a user.
     *
     * Returns all token IDs currently active for the specified user.
     *
     * @param  int|string  $userId  The user ID
     * @return array<string> Array of token IDs
     */
    public function getUserTokens(int|string $userId): array
    {
        $redis = $this->getConnection();
        $userTokensKey = self::USER_TOKENS_PREFIX . $userId;

        return $redis->smembers($userTokensKey);
    }

    /**
     * Count active tokens for a user.
     *
     * Returns the total number of active tokens belonging to the user.
     *
     * @param  int|string  $userId  The user ID
     * @return int Total number of active tokens
     */
    public function countUserTokens(int|string $userId): int
    {
        $redis = $this->getConnection();
        $userTokensKey = self::USER_TOKENS_PREFIX . $userId;

        return (int) $redis->scard($userTokensKey);
    }

    /**
     * Clean up expired tokens for a user.
     *
     * Removes expired token entries from the user's active tokens set.
     *
     * @param  int|string  $userId  The user ID
     * @return int Number of tokens cleaned up
     */
    public function cleanupExpiredTokens(int|string $userId): int
    {
        $redis = $this->getConnection();
        $userTokensKey = self::USER_TOKENS_PREFIX . $userId;
        $tokenIds = $redis->smembers($userTokensKey);
        $cleaned = 0;

        foreach ($tokenIds as $tokenId) {
            $tokenKey = self::TOKEN_PREFIX . $tokenId;

            if (! $redis->exists($tokenKey)) {
                $redis->srem($userTokensKey, $tokenId);
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Create a new token and store its metadata.
     *
     * Generates a new Sanctum token and stores its metadata in Redis.
     *
     * @param  User  $user  The user to create token for
     * @param  string  $name  The token name (default: 'auth_token')
     * @param  int  $ttl  Time to live in seconds (default: 7 days)
     * @return NewAccessToken The created access token
     */
    public function createToken(User $user, string $name = 'auth_token', int $ttl = 604800): NewAccessToken
    {
        $token = $user->createToken($name);

        $tokenId = hash('sha256', explode('|', $token->plainTextToken, 2)[1]);

        $userId = $user->getKey();
        if (is_string($userId) || is_int($userId)) {
            $this->storeTokenMetadata($tokenId, (string) $userId, $ttl);
        }

        return $token;
    }

    /**
     * Extract token ID from plain text token.
     *
     * Extracts and hashes the token part from the Sanctum plain text token format.
     *
     * @param  string  $plainTextToken  The plain text token in format 'id|token'
     * @return string The hashed token ID
     */
    public function extractTokenId(string $plainTextToken): string
    {
        $token = explode('|', $plainTextToken, 2)[1] ?? $plainTextToken;

        return hash('sha256', $token);
    }
}
