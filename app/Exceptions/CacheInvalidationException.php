<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class CacheInvalidationException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(
        protected string $key,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: "Falha ao invalidar cache para a chave: {$this->key}";
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => 'Erro ao limpar cache.',
            'error' => 'Cache invalidation failed',
        ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Factory method for deletion failures.
     */
    public static function deletionFailed(string $key): self
    {
        return new self($key, "Falha ao deletar cache com prefixo: {$key}");
    }

    /**
     * Get the cache key.
     */
    public function getCacheKey(): string
    {
        return $this->key;
    }
}
