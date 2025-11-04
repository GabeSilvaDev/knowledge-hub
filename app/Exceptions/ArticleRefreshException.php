<?php

namespace App\Exceptions;

use RuntimeException;

class ArticleRefreshException extends RuntimeException
{
    public static function failedToRefresh(): self
    {
        return new self('Failed to refresh article after update');
    }
}
