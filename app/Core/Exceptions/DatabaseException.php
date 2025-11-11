<?php

namespace App\Core\Exceptions;

use Exception;

/**
 * Database-specific exception class
 * 
 * Thrown when database operations fail, providing context
 * about the specific database error that occurred.
 */
class DatabaseException extends Exception
{
    /**
     * Create a new database exception
     * 
     * @param string $message The error message
     * @param int $code The error code (default: 0)
     * @param Exception|null $previous The previous exception for chaining
     */
    public function __construct(string $message, int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
