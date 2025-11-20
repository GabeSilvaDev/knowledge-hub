<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Exception thrown when article refresh fails.
 *
 * Indicates that an article could not be refreshed from database after an update operation.
 */
class ArticleRefreshException extends RuntimeException
{
    /**
     * Create exception for failed article refresh.
     *
     * Factory method to create a new exception instance with standard message.
     *
     * @return self The exception instance
     */
    public static function failedToRefresh(): self
    {
        return new self('Failed to refresh article after update');
    }
}
