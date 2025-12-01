<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Exception thrown when cache invalidation fails.
 *
 * Indicates that the system failed to clear cache entries for a specific key or prefix.
 */
class CacheInvalidationException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * Constructs the exception with the cache key and optional custom message.
     *
     * @param  string  $key  The cache key that failed to invalidate
     * @param  string  $message  Optional custom error message
     * @param  int  $code  Optional exception code
     * @param  Throwable|null  $previous  Optional previous exception for chaining
     */
    public function __construct(
        protected string $key,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: "Failed to invalidate cache for key: {$this->key}";
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     *
     * Returns a JSON response with error details and 500 status code.
     *
     * @return JsonResponse The JSON error response
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Failed to clear cache.',
            'error' => 'Cache invalidation failed',
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Factory method for deletion failures.
     *
     * Creates a new exception instance for cache deletion failures.
     *
     * @param  string  $key  The cache key prefix that failed to delete
     * @return self The exception instance
     */
    public static function deletionFailed(string $key): self
    {
        return new self($key, "Failed to delete cache with prefix: {$key}");
    }

    /**
     * Get the cache key.
     *
     * Returns the cache key that caused the invalidation failure.
     *
     * @return string The cache key
     */
    public function getCacheKey(): string
    {
        return $this->key;
    }
}
