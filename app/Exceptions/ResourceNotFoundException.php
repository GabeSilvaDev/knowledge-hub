<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Throwable;

/**
 * Exception thrown when a requested resource is not found.
 *
 * Used for consistent handling of 404 errors across the application.
 */
class ResourceNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     *
     * Constructs the exception with the resource name and optional custom message.
     *
     * @param  string  $resourceName  The name of the resource that was not found
     * @param  string  $message  Optional custom error message
     * @param  int  $code  Optional exception code
     * @param  Throwable|null  $previous  Optional previous exception for chaining
     */
    public function __construct(
        protected string $resourceName,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null
    ) {
        $message = $message ?: "The requested resource ({$this->resourceName}) was not found.";
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     *
     * Returns a JSON response with error details and 404 status code.
     *
     * @return JsonResponse The JSON error response
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
     *
     * Returns the name of the resource that was not found.
     *
     * @return string The resource name
     */
    public function getResourceName(): string
    {
        return $this->resourceName;
    }
}
