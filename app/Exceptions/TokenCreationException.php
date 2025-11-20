<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception thrown when token creation fails.
 *
 * Indicates that the system failed to create an authentication token.
 */
class TokenCreationException extends RuntimeException
{
    /**
     * Create exception for failed token creation.
     *
     * Factory method to create a new exception instance with standard message.
     *
     * @return self The exception instance
     */
    public static function failedToCreate(): self
    {
        return new self('Failed to create token');
    }
}
