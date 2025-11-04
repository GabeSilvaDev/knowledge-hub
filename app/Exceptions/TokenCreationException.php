<?php

namespace App\Exceptions;

use RuntimeException;

class TokenCreationException extends RuntimeException
{
    public static function failedToCreate(): self
    {
        return new self('Failed to create token');
    }
}
