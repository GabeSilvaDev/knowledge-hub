<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Self Follow Exception.
 *
 * Thrown when a user attempts to follow themselves.
 */
class SelfFollowException extends Exception
{
    /**
     * Create a new self follow exception instance.
     *
     * @param  string  $message  The exception message
     */
    public function __construct(string $message = 'You cannot follow yourself.')
    {
        parent::__construct($message);
    }

    /**
     * Create an exception for self-follow attempt.
     *
     * @return self The exception instance
     */
    public static function cannotFollowSelf(): self
    {
        return new self('You cannot follow yourself.');
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return JsonResponse The JSON error response
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], JsonResponse::HTTP_BAD_REQUEST);
    }
}
