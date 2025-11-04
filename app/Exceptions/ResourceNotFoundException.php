<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

class ResourceNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(
        protected string $resourceName,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: "O recurso solicitado ({$this->resourceName}) não foi encontrado.";
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error' => 'Resource not found',
        ], JsonResponse::HTTP_NOT_FOUND);
    }

    /**
     * Get the resource name.
     */
    public function getResourceName(): string
    {
        return $this->resourceName;
    }
}
