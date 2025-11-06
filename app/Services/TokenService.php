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
     */
    private function getConnection(): Connection
    {
        return Redis::connection('tokens');
    }

    /**
     * Store token metadata in Redis.
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
     * @return array<string, mixed>|null
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
     * @return array<string>
     */
    public function getUserTokens(int|string $userId): array
    {
        $redis = $this->getConnection();
        $userTokensKey = self::USER_TOKENS_PREFIX . $userId;

        return $redis->smembers($userTokensKey);
    }

    /**
     * Count active tokens for a user.
     */
    public function countUserTokens(int|string $userId): int
    {
        $redis = $this->getConnection();
        $userTokensKey = self::USER_TOKENS_PREFIX . $userId;

        return (int) $redis->scard($userTokensKey);
    }

    /**
     * Clean up expired tokens for a user.
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
     */
    public function extractTokenId(string $plainTextToken): string
    {
        $token = explode('|', $plainTextToken, 2)[1] ?? $plainTextToken;

        return hash('sha256', $token);
    }
}
